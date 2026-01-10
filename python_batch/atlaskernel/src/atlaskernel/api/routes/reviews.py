from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel, Field
from typing import Literal, Optional, List
from sqlalchemy.orm import Session

from atlaskernel.db.session import get_db
from atlaskernel.application.review.resolve_review import (
    ResolveReviewService, ResolveReviewInput, VerificationInput
)

router = APIRouter(prefix="/v1", tags=["reviews"])

class VerificationPayload(BaseModel):
    type: Literal["human", "ai", "hybrid"]
    verified_by: str
    model: Optional[str] = None
    sources: List[str] = Field(default_factory=list)
    confidence: float = 0.0
    note: Optional[str] = None

class ResolvePayload(BaseModel):
    action: Literal["accept", "override", "reject"]
    canonical_value: Optional[str] = None
    verification: VerificationPayload

@router.post("/reviews/{review_id}/resolve")
def resolve_review(review_id: int, payload: ResolvePayload, db: Session = Depends(get_db)):
    svc = ResolveReviewService(db)
    try:
        inp = ResolveReviewInput(
            action=payload.action,
            canonical_value=payload.canonical_value,
            verification=VerificationInput(
                type=payload.verification.type,
                verified_by=payload.verification.verified_by,
                model=payload.verification.model,
                sources=payload.verification.sources,
                confidence=payload.verification.confidence,
                note=payload.verification.note,
            ),
        )
        return svc.handle(review_id, inp)
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"internal error: {e}")