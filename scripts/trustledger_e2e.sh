#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-https://localhost}"
ORIGIN="${ORIGIN:-https://localhost}"
REFERER="${REFERER:-https://localhost}"
EMAIL="${EMAIL:-valid.email@example.com}"
PASSWORD="${PASSWORD:-testtest1}"
SHOP_ID="${SHOP_ID:-1}"
ITEM_ID="${ITEM_ID:-2}"
AMOUNT="${AMOUNT:-5000}"
REFUND_AMOUNT="${REFUND_AMOUNT:-800}"
HOLD_AMOUNT="${HOLD_AMOUNT:-1000}"
PAYOUT_AMOUNT_OK="${PAYOUT_AMOUNT_OK:-2000}"
PAYOUT_AMOUNT_FAIL="${PAYOUT_AMOUNT_FAIL:-1500}"
FROM_DATE="${FROM_DATE:-2026-01-01}"
TO_DATE="${TO_DATE:-2026-12-31}"
COOKIE_FILE="${COOKIE_FILE:-cookie.txt}"

REQ_HEADERS=(-H "Origin: ${ORIGIN}" -H "Referer: ${REFERER}" -H "Accept: application/json")
REQ_JSON_HEADERS=("${REQ_HEADERS[@]}" -H "Content-Type: application/json")

log() { printf "\n[%s] %s\n" "$(date '+%H:%M:%S')" "$*"; }

need() {
  command -v "$1" >/dev/null 2>&1 || { echo "Missing command: $1"; exit 1; }
}

need curl
need python3
need docker
need stripe

log "== 0) Preflight =="
log "BASE_URL=$BASE_URL"
log "Cookie file: $COOKIE_FILE"

# ---------------------------------------
# Helpers
# ---------------------------------------
curl_json() {
  local method="$1"
  local url="$2"
  local body="${3:-}"

  local tmp
  tmp="$(mktemp)"
  local code

  if [[ -n "$body" ]]; then
    code="$(curl -k -s -S -o "$tmp" -w "%{http_code}" -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
      "${REQ_JSON_HEADERS[@]}" -X "$method" "$url" -d "$body")"
  else
    code="$(curl -k -s -S -o "$tmp" -w "%{http_code}" -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
      "${REQ_HEADERS[@]}" -X "$method" "$url")"
  fi

  local out
  out="$(cat "$tmp")"
  rm -f "$tmp"

  if [[ "$code" != 2* ]]; then
    echo "HTTP $code for $method $url" >&2
    echo "$out" >&2
    exit 1
  fi

  echo "$out"
}

curl_get() {
  local url="$1"
  local tmp
  tmp="$(mktemp)"
  local code
  code="$(curl -k -s -S -o "$tmp" -w "%{http_code}" -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
    "${REQ_HEADERS[@]}" "$url")"

  local out
  out="$(cat "$tmp")"
  rm -f "$tmp"

  if [[ "$code" != 2* ]]; then
    echo "HTTP $code for GET $url" >&2
    echo "$out" >&2
    exit 1
  fi

  echo "$out"
}

json_get() {
  python3 -c '
import sys, json
path = sys.argv[1].split(".")
raw = sys.stdin.read()
if not raw or not raw.strip():
    sys.stderr.write("json_get: empty input\n")
    sys.exit(1)

try:
    d = json.loads(raw)
except Exception:
    sys.stderr.write("json_get: input is not JSON\n")
    sys.stderr.write(raw[:300] + "\n")
    sys.exit(1)

cur = d
for k in path:
    if isinstance(cur, dict) and k in cur:
        cur = cur[k]
    else:
        sys.stderr.write(f"json_get: missing key: {k}\n")
        sys.exit(1)

if isinstance(cur, (dict, list)):
    print(json.dumps(cur, ensure_ascii=False))
else:
    print(cur)
' "$1"
}

mysql_exec() {
  local sql="$1"
  local db_name db_user db_pass
  db_name="$(docker compose exec -T php printenv DB_DATABASE | tr -d '\r')"
  db_user="$(docker compose exec -T php printenv DB_USERNAME | tr -d '\r')"
  db_pass="$(docker compose exec -T php printenv DB_PASSWORD | tr -d '\r')"
  docker compose exec -T mysql mysql -u"$db_user" -p"$db_pass" "$db_name" -e "$sql"
}

assert_contains() {
  local hay="$1"
  local needle="$2"
  echo "$hay" | grep -q "$needle" || { echo "ASSERT FAILED: expected to contain: $needle"; exit 1; }
}

assert_eq_int() {
  local a="$1"
  local b="$2"
  if [[ "$a" != "$b" ]]; then
    echo "ASSERT FAILED: $a != $b"
    exit 1
  fi
}

# ---------------------------------------
# 1) Login (Sanctum)
# ---------------------------------------
log "== 1) Login =="
curl -k -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" "${REQ_HEADERS[@]}" "$BASE_URL/sanctum/csrf-cookie" > /dev/null

XSRF=$(python3 -c 'import http.cookiejar,urllib.parse; cj=http.cookiejar.MozillaCookieJar("'"$COOKIE_FILE"'"); cj.load(); print(next(urllib.parse.unquote(c.value) for c in cj if c.name=="XSRF-TOKEN"))')
curl -k -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
  -H "Origin: ${ORIGIN}" -H "Referer: ${REFERER}" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: ${XSRF}" \
  -X POST "$BASE_URL/login" \
  -d "{\"email\":\"${EMAIL}\",\"password\":\"${PASSWORD}\"}" > /dev/null

ME_JSON="$(curl_get "$BASE_URL/api/me")"
assert_contains "$ME_JSON" '"id":'
log "Logged in OK: $(echo "$ME_JSON" | head -c 120)…"

# ---------------------------------------
# 2) Wallet payment methods must exist (one-click needs it)
# ---------------------------------------
log "== 2) Wallet payment methods =="
PM_JSON="$(curl_get "$BASE_URL/api/wallet/payment-methods")"
assert_contains "$PM_JSON" '"exists":true'
log "Wallet: $PM_JSON"

# ---------------------------------------
# 3) Create order -> address -> confirm -> one-click
# ---------------------------------------
log "== 3) Order -> OneClick (v2 sale/fee pipeline) =="
ORDER_JSON="$(curl_json POST "$BASE_URL/api/orders" "{
  \"shop_id\": ${SHOP_ID},
  \"items\": [
    {\"item_id\": ${ITEM_ID}, \"name\": \"HDD\", \"price_amount\": ${AMOUNT}, \"price_currency\": \"JPY\", \"quantity\": 1, \"image_path\": \"item_images/HDD+Hard+Disk.jpg\"}
  ],
  \"meta\": null
}")"
ORDER_ID="$(echo "$ORDER_JSON" | python3 -c 'import sys,json; d=json.load(sys.stdin); print(d.get("order_id") or d.get("orderId") or d.get("id"))')"
log "Order created: ORDER_ID=$ORDER_ID"

ADDR_JSON="$(curl_get "$BASE_URL/api/me/addresses/primary")"
ADDR_ID="$(echo "$ADDR_JSON" | python3 -c 'import sys,json; d=json.load(sys.stdin); print(d["data"]["id"])')"
log "Primary address: ADDR_ID=$ADDR_ID"

curl_json POST "$BASE_URL/api/orders/${ORDER_ID}/address" "{\"address_id\": ${ADDR_ID}}" > /dev/null
curl_json POST "$BASE_URL/api/orders/${ORDER_ID}/confirm" "" > /dev/null

ONECLICK_JSON="$(curl_json POST "$BASE_URL/api/wallet/one-click-checkout" "{\"order_id\": ${ORDER_ID}}")"
log "OneClick raw (may be empty): ${ONECLICK_JSON:-<empty>}"

# ① OneClickがJSONを返す場合はそのまま使う
if [[ -n "${ONECLICK_JSON:-}" ]]; then
  PI_ID="$(json_get provider_payment_id <<<"$ONECLICK_JSON")"
PAYMENT_ID="$(json_get payment_id <<<"$ONECLICK_JSON")"
  assert_contains "$ONECLICK_JSON" '"requires_action":false'
else
  PI_ID=""
  PAYMENT_ID=""
fi

# ② どのみち /api/me/orders/{id} で確定値を取る（Webhook遅延にも強い）
#    order_status が paid になるまで最大10秒待つ
for i in {1..10}; do
  ORDER_READ="$(curl_get "$BASE_URL/api/me/orders/${ORDER_ID}")"
  STATUS="$(echo "$ORDER_READ" | json_get order_status || true)"

  if [[ "$STATUS" == "paid" ]]; then
    # order detail の構造：payment.payment_id / payment.provider_payment_id
    PI_ID="$(echo "$ORDER_READ" | json_get payment.provider_payment_id)"
    PAYMENT_ID="$(echo "$ORDER_READ" | json_get payment.payment_id)"
    break
  fi
  sleep 1
done

# 最終ガード
if [[ -z "${PI_ID:-}" || -z "${PAYMENT_ID:-}" ]]; then
  echo "OneClick verification failed: could not resolve payment from order detail" >&2
  echo "$ORDER_READ" >&2
  exit 1
fi

log "OneClick resolved: payment_id=$PAYMENT_ID provider_payment_id=$PI_ID"

# ---------------------------------------
# 4) v2 DB checks: sale/fee postings exist + double-entry rows
# ---------------------------------------
log "== 4) v2 DB checks (sale + fee postings, double-entry) =="
POSTINGS="$(mysql_exec "select posting_type, source_event_id, amount, currency, order_id, payment_id from ledger_postings order by id desc limit 30;")"
echo "$POSTINGS"
assert_contains "$POSTINGS" ":sale"
assert_contains "$POSTINGS" ":fee"

ENTRIES="$(mysql_exec "select posting_id, account_code, side, amount, currency from ledger_entries order by id desc limit 60;")"
echo "$ENTRIES"
assert_contains "$ENTRIES" "CASH_CLEARING"
assert_contains "$ENTRIES" "SALES_REVENUE"
assert_contains "$ENTRIES" "FEE_EXPENSE"

# ---------------------------------------
# 5) v2 API checks: summary/entries
# ---------------------------------------
log "== 5) v2 API checks (summary / entries) =="
SUMMARY="$(curl_get "$BASE_URL/api/ledger/summary?shop_id=${SHOP_ID}&from=${FROM_DATE}&to=${TO_DATE}")"
ENT_LIST="$(curl_get "$BASE_URL/api/ledger/entries?shop_id=${SHOP_ID}&from=${FROM_DATE}&to=${TO_DATE}&limit=20")"
assert_contains "$SUMMARY" '"sales_total":'
assert_contains "$ENT_LIST" '"items":'
log "Summary: $SUMMARY"
log "Entries:  $(echo "$ENT_LIST" | head -c 160)…"

# ---------------------------------------
# 6) v2-4 reconciliation + replay (must end with 0)
# ---------------------------------------
log "== 6) v2 reconciliation/replay =="
REC="$(curl_get "$BASE_URL/api/ledger/reconciliation?shop_id=${SHOP_ID}&from=${FROM_DATE}&to=${TO_DATE}&limit=50")"
log "Reconciliation: $REC"
MISS_CNT="$(echo "$REC" | json_get missing_sale_count)"
if [[ -z "$MISS_CNT" ]]; then
  echo "Could not parse missing_sale_count"
  exit 1
fi

# If missing, replay each payment_id
if [[ "$MISS_CNT" != "0" ]]; then
  log "Missing sales detected: $MISS_CNT -> replay all"
  python3 - <<'PY'
import json, subprocess, os, sys
rec = json.loads(os.environ["REC_JSON"])
base = os.environ["BASE_URL"]
cookie = os.environ["COOKIE_FILE"]
origin = os.environ["ORIGIN"]
referer = os.environ["REFERER"]

for item in rec.get("missing_sales", []):
    pid = item.get("payment_id")
    if not pid:
        continue
    cmd = [
      "curl","-k","-s","-b",cookie,
      "-H",f"Origin: {origin}",
      "-H",f"Referer: {referer}",
      "-H","Accept: application/json",
      "-H","Content-Type: application/json",
      "-X","POST",f"{base}/api/ledger/replay/sale",
      "-d",json.dumps({"payment_id": int(pid)})
    ]
    out = subprocess.check_output(cmd).decode("utf-8")
    print("replay", pid, out.strip())
PY
  export REC_JSON="$REC" BASE_URL ORIGIN REFERER COOKIE_FILE

  REC2="$(curl_get "$BASE_URL/api/ledger/reconciliation?shop_id=${SHOP_ID}&from=${FROM_DATE}&to=${TO_DATE}&limit=50")"
  log "Reconciliation after replay: $REC2"
  assert_contains "$REC2" '"missing_sale_count":0'
else
  log "No missing sales. OK."
fi

# ---------------------------------------
# 7) v3-1 balance recalc + get
# ---------------------------------------
log "== 7) v3-1 balance recalc (shop -> account) =="
RECALC="$(curl_json POST "$BASE_URL/api/shops/${SHOP_ID}/balance/recalculate?from=${FROM_DATE}&to=${TO_DATE}" "")"
assert_contains "$RECALC" '"account_id":'
ACCOUNT_ID="$(echo "$RECALC" | json_get account_id)"
log "Recalculate OK: account_id=$ACCOUNT_ID"

BAL_JSON="$(curl_get "$BASE_URL/api/accounts/${ACCOUNT_ID}/balance")"
assert_contains "$BAL_JSON" '"available_amount":'
assert_contains "$BAL_JSON" '"held_amount":'
assert_contains "$BAL_JSON" '"pending_amount":'
log "Balance: $BAL_JSON"

# ---------------------------------------
# 8) v3-2 hold create/release
# ---------------------------------------
log "== 8) v3-2 hold create/release =="
HOLD_CREATE="$(curl_json POST "$BASE_URL/api/accounts/${ACCOUNT_ID}/holds" "{\"amount\": ${HOLD_AMOUNT}, \"currency\": \"JPY\", \"reason_code\": \"shipment_pending\"}")"
assert_contains "$HOLD_CREATE" '"hold_id":'
HOLD_ID="$(echo "$HOLD_CREATE" | json_get hold_id)"
log "Hold created: hold_id=$HOLD_ID"

BAL1="$(curl_get "$BASE_URL/api/accounts/${ACCOUNT_ID}/balance")"
log "Balance after hold: $BAL1"
assert_contains "$BAL1" '"held_amount":'

curl_json POST "$BASE_URL/api/holds/${HOLD_ID}/release" "" > /dev/null
BAL2="$(curl_get "$BASE_URL/api/accounts/${ACCOUNT_ID}/balance")"
log "Balance after release: $BAL2"

# ---------------------------------------
# 9) v3-3 payout request/paid/failed
# ---------------------------------------
log "== 9) v3-3 payout request -> processing -> paid =="
PAYOUT1="$(curl_json POST "$BASE_URL/api/accounts/${ACCOUNT_ID}/payouts" "{\"amount\": ${PAYOUT_AMOUNT_OK}, \"currency\": \"JPY\", \"rail\": \"manual\"}")"
assert_contains "$PAYOUT1" '"payout_id":'
PAYOUT_ID1="$(echo "$PAYOUT1" | json_get payout_id)"
log "Payout requested: payout_id=$PAYOUT_ID1"

curl_json POST "$BASE_URL/api/payouts/${PAYOUT_ID1}/status" "{\"status\":\"processing\"}" > /dev/null
curl_json POST "$BASE_URL/api/payouts/${PAYOUT_ID1}/status" "{\"status\":\"paid\"}" > /dev/null
log "Payout paid OK."

log "== 9b) v3-3 payout request -> failed (pending returns) =="
PAYOUT2="$(curl_json POST "$BASE_URL/api/accounts/${ACCOUNT_ID}/payouts" "{\"amount\": ${PAYOUT_AMOUNT_FAIL}, \"currency\": \"JPY\", \"rail\": \"manual\"}")"
assert_contains "$PAYOUT2" '"payout_id":'
PAYOUT_ID2="$(echo "$PAYOUT2" | json_get payout_id)"
log "Payout requested (for failed): payout_id=$PAYOUT_ID2"

# record balance after request
BAL_REQ="$(curl_get "$BASE_URL/api/accounts/${ACCOUNT_ID}/balance")"
log "Balance after payout request: $BAL_REQ"

curl_json POST "$BASE_URL/api/payouts/${PAYOUT_ID2}/status" "{\"status\":\"failed\"}" > /dev/null
BAL_FAIL="$(curl_get "$BASE_URL/api/accounts/${ACCOUNT_ID}/balance")"
log "Balance after payout failed: $BAL_FAIL"

log "✅ All TrustLedger E2E checks passed."
