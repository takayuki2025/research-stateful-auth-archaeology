import os
import json
from pathlib import Path
from datetime import datetime, timezone

class AuditLogger:
    def __init__(self, path: str | None = None):
        self.path = Path(path or os.getenv("ATLAS_AUDIT_PATH", "var/log/atlaskernel_audit.jsonl"))

    def log(self, payload: dict):
        self.path.parent.mkdir(parents=True, exist_ok=True)
        record = {
            "ts": datetime.now(timezone.utc).isoformat(),
            "schema_version": payload.get("schema_version"),
            "engine_version": payload.get("engine_version"),
            "entity_type": payload.get("entity_type"),
            "decision": payload.get("decision"),
            "confidence": payload.get("confidence"),
            "payload": payload,
        }
        with self.path.open("a", encoding="utf-8") as f:
            f.write(json.dumps(record, ensure_ascii=False) + "\n")