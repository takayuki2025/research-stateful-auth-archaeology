#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8081}"
HEALTH_URL="${HEALTH_URL:-http://localhost:${PORT}/actuator/health}"
LOG_FILE="${LOG_FILE:-paymentcore.log}"
PID_FILE="${PID_FILE:-paymentcore.pid}"

# ---- helpers ----
log() { printf "[%s] %s\n" "$(date '+%H:%M:%S')" "$*"; }

stop_port_listener() {
  local pid
  pid="$(lsof -nP -iTCP:${PORT} -sTCP:LISTEN -t 2>/dev/null || true)"
  if [[ -n "${pid}" ]]; then
    log "Stopping process listening on :${PORT} (PID=${pid})"
    kill -TERM "${pid}" 2>/dev/null || true

    # wait up to 5s
    for _ in {1..50}; do
      if ! lsof -nP -iTCP:${PORT} -sTCP:LISTEN >/dev/null 2>&1; then
        break
      fi
      sleep 0.1
    done
  fi
}

stop_pid_file() {
  if [[ -f "${PID_FILE}" ]]; then
    local pid
    pid="$(cat "${PID_FILE}" 2>/dev/null || true)"
    if [[ -n "${pid}" ]]; then
      log "Stopping pid from ${PID_FILE}: ${pid}"
      kill -TERM "${pid}" 2>/dev/null || true
    fi
    rm -f "${PID_FILE}" || true
  fi
}

wait_health() {
  log "Waiting health: ${HEALTH_URL}"
  for i in {1..40}; do
    if curl -s "${HEALTH_URL}" | grep -q '"status":"UP"'; then
      log "Health is UP."
      return 0
    fi
    sleep 0.5
  done
  log "Health did not become UP. Tail logs:"
  tail -n 80 "${LOG_FILE}" || true
  return 1
}

# ---- main ----
cd "$(dirname "$0")/.."  # move to payment_core root

log "== Restart PaymentCore =="
stop_pid_file
stop_port_listener

log "== Build (clean) =="
./gradlew clean > /dev/null

log "== BootRun (background) =="
nohup ./gradlew bootRun --no-daemon > "${LOG_FILE}" 2>&1 &
echo $! > "${PID_FILE}"
log "Started: PID=$(cat "${PID_FILE}")  LOG=${LOG_FILE}"

wait_health

log "DONE"