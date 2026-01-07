import json
from pathlib import Path
from jsonschema import Draft202012Validator

_SCHEMA_CACHE = {}

def validate_entity_analysis_v1(payload: dict) -> None:
    schema_path = Path(__file__).resolve().parents[3] / "schema" / "entity_analysis.v1.json"
    schema_key = str(schema_path)

    if schema_key not in _SCHEMA_CACHE:
        schema = json.loads(schema_path.read_text(encoding="utf-8"))
        _SCHEMA_CACHE[schema_key] = Draft202012Validator(schema)

    validator = _SCHEMA_CACHE[schema_key]
    errors = sorted(validator.iter_errors(payload), key=lambda e: e.path)

    if errors:
        msg = "; ".join([f"{list(e.path)}: {e.message}" for e in errors[:5]])
        raise ValueError(f"Schema validation failed: {msg}")