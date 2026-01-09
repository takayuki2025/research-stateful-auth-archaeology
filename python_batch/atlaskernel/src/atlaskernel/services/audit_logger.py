from datetime import datetime, timezone
from typing import Dict, Any

def audit_log(event: str, payload: Dict[str, Any] | None = None) -> None:
    print("[AUDIT]", {
        "ts": datetime.now(timezone.utc).isoformat(),
        "event": event,
        "payload": payload or {},
    })