import json
from sqlalchemy import text
from sqlalchemy.orm import Session

class KnowledgeRepository:
    def __init__(self, db: Session):
        self.db = db

    def upsert_entity(self, entity_type: str, canonical_value: str) -> int:
        # uq_entity_canonical (entity_type, canonical_value) がある前提
        self.db.execute(
            text("""
                INSERT INTO knowledge_entities (entity_type, canonical_value, status, created_at, updated_at)
                VALUES (:t, :v, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()
            """),
            {"t": entity_type, "v": canonical_value},
        )
        row = self.db.execute(
            text("""
                SELECT id FROM knowledge_entities
                WHERE entity_type = :t AND canonical_value = :v
            """),
            {"t": entity_type, "v": canonical_value},
        ).mappings().first()
        return int(row["id"])

    def add_verification(
        self,
        knowledge_entity_id: int,
        verification_type: str,
        verified_by: str,
        verification_model: str | None,
        verification_date,
        sources: list[str],
        confidence: float,
        note: str | None,
    ) -> int:
        res = self.db.execute(
            text("""
                INSERT INTO knowledge_verifications
                (knowledge_entity_id, verification_type, verified_by, verification_model, verification_date,
                 sources_json, confidence, note, created_at)
                VALUES (:kid, :vt, :vb, :vm, :vd, :src, :conf, :note, NOW())
            """),
            {
                "kid": knowledge_entity_id,
                "vt": verification_type,
                "vb": verified_by,
                "vm": verification_model,
                "vd": verification_date,
                "src": json.dumps(sources, ensure_ascii=False),
                "conf": confidence,
                "note": note,
            },
        )
        return int(res.lastrowid or 0)