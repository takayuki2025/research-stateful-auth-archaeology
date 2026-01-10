from pydantic import BaseModel
from typing import List, Optional, Dict, Any

class Candidate(BaseModel):
    value: str
    score: float

class ReviewItem(BaseModel):
    id: int
    entity_type: str
    raw_value: str
    decision: str
    confidence: float
    rule_id: str
    canonical_value: Optional[str]

    candidates: List[Candidate]
    policy_trace: Optional[Dict[str, Any]] = None
    knowledge_sources: Optional[Dict[str, Any]] = None

    created_at: str

class ReviewListResponse(BaseModel):
    items: List[ReviewItem]
    next_cursor: Optional[int] = None


class ResolveRequest(BaseModel):
    action: str  # accept / reject / override
    canonical_value: Optional[str] = None
    note: Optional[str] = None


class ResolveResponse(BaseModel):
    ok: bool
    item: ReviewItem