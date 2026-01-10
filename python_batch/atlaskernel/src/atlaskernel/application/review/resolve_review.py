from dataclasses import dataclass
from datetime import datetime
from sqlalchemy.orm import Session

from atlaskernel.infrastructure.persistence.review_repository import ReviewRepository
from atlaskernel.infrastructure.persistence.knowledge_repository import KnowledgeRepository

@dataclass
class VerificationInput:
    type: str                 # human / ai / hybrid
    verified_by: str          # admin:1 / gpt:xxx
    model: str | None         # GPT-4.1 / WebSearch etc
    sources: list[str]
    confidence: float
    note: str | None = None
    verification_date: datetime | None = None

@dataclass
class ResolveReviewInput:
    action: str               # accept / override / reject
    canonical_value: str | None
    verification: VerificationInput

class ResolveReviewService:
    def __init__(self, db: Session):
        self.db = db
        self.reviews = ReviewRepository(db)
        self.knowledge = KnowledgeRepository(db)

    def handle(self, review_id: int, inp: ResolveReviewInput) -> dict:
        task = self.reviews.get_by_id(review_id)
        if not task:
            raise ValueError("review_task not found")
        if task["status"] != "pending":
            raise ValueError("review_task is not pending")

        entity_type = task["entity_type"]
        action = inp.action

        resolved_value = None
        knowledge_entity_id = None

        if action in ("accept", "override"):
            if not inp.canonical_value:
                raise ValueError("canonical_value required for accept/override")
            resolved_value = inp.canonical_value

            # knowledge_entities upsert
            knowledge_entity_id = self.knowledge.upsert_entity(entity_type, resolved_value)

            # knowledge_verifications insert (履歴は積み上げ)
            vd = inp.verification.verification_date or datetime.utcnow()
            self.knowledge.add_verification(
                knowledge_entity_id=knowledge_entity_id,
                verification_type=inp.verification.type,
                verified_by=inp.verification.verified_by,
                verification_model=inp.verification.model,
                verification_date=vd,
                sources=inp.verification.sources,
                confidence=inp.verification.confidence,
                note=inp.verification.note,
            )

        # review_tasks + review_events
        self.reviews.mark_resolved(
            review_id=review_id,
            action=action,
            resolved_value=resolved_value,
            note=inp.verification.note,
        )

        self.db.commit()

        return {
            "review_id": review_id,
            "status": "resolved",
            "entity_type": entity_type,
            "canonical_value": resolved_value,
            "knowledge_entity_id": knowledge_entity_id,
        }