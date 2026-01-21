#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE:-http://localhost}"
SHOP_CODE="${SHOP_CODE:-shop-a}"
EMAIL="${EMAIL:-valid.email@example.com}"
PASSWORD="${PASSWORD:-testtest1}"

# Bearer token (provider別に差し替える)
TOKEN="${TOKEN:-}"

# Cookie jar
COOKIE_JAR="${COOKIE_JAR:-cookies.txt}"

hr() { echo "------------------------------------------------------------"; }
title() { echo; hr; echo "$1"; hr; }

# ---- helpers ----
get_csrf_cookie() {
  curl -s -i -c "$COOKIE_JAR" "$BASE/sanctum/csrf-cookie" >/dev/null
}

xsrf_token() {
  grep -E '\sXSRF-TOKEN\s' "$COOKIE_JAR" | tail -n 1 | awk '{print $7}' \
    | python3 -c 'import sys,urllib.parse; print(urllib.parse.unquote(sys.stdin.read().strip()))'
}

login_cookie() {
  get_csrf_cookie
  local xsrf
  xsrf="$(xsrf_token)"

  curl -s -i -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "X-XSRF-TOKEN: $xsrf" \
    -H "X-Requested-With: XMLHttpRequest" \
    -H "Origin: $BASE" \
    -H "Referer: $BASE" \
    -X POST "$BASE/login" \
    -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}" \
    | awk 'NR==1{print}'
}

cookie_get() {
  local path="$1"
  curl -s -i -b "$COOKIE_JAR" \
    -H "Accept: application/json" \
    -H "Origin: $BASE" \
    -H "Referer: $BASE" \
    "$BASE$path" \
    | awk 'NR==1{print}'
}

bearer_get() {
  local path="$1"
  curl -s -i \
    -H "Accept: application/json" \
    -H "Authorization: Bearer $TOKEN" \
    "$BASE$path" \
    | awk 'NR==1{print}'
}

noauth_get() {
  local path="$1"
  curl -s -i -H "Accept: application/json" "$BASE$path" | awk 'NR==1{print}'
}

tamper_token() {
  echo "x$TOKEN"
}

# ---- tests ----
title "A) Sanctum (cookie) login"
echo "Login status:"
login_cookie

title "B) Cookie contract tests"
echo "/api/me:"
cookie_get "/api/me"
echo "/api/shops/me:"
cookie_get "/api/shops/me"
echo "/api/shops/$SHOP_CODE/dashboard/orders:"
cookie_get "/api/shops/$SHOP_CODE/dashboard/orders"

title "C) No-auth negative tests"
echo "/api/me (no token):"
noauth_get "/api/me"
echo "/api/shops/me (no token):"
noauth_get "/api/shops/me"
echo "/api/shops/$SHOP_CODE/dashboard/orders (no token):"
noauth_get "/api/shops/$SHOP_CODE/dashboard/orders"

if [[ -n "$TOKEN" ]]; then
  title "D) Bearer contract tests"
  echo "/api/me:"
  bearer_get "/api/me"
  echo "/api/shops/me:"
  bearer_get "/api/shops/me"
  echo "/api/shops/$SHOP_CODE/dashboard/orders:"
  bearer_get "/api/shops/$SHOP_CODE/dashboard/orders"

  title "E) Tampered token should be 401"
  BAD="$(tamper_token)"
  curl -s -i -H "Accept: application/json" \
    -H "Authorization: Bearer $BAD" \
    "$BASE/api/me" | awk 'NR==1{print}'
else
  title "D/E) Bearer tests skipped (TOKEN is empty)"
fi

title "DONE"