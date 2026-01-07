from pydantic import BaseModel, Field
from typing import List, Dict, Optional, Literal
from .candidate import Candidate

DecisionType = Literal["auto_accept", "needs_review", "rejected"]


class AnalysisResult(BaseModel):
    schema_version: str = Field(default="entity_analysis.v1")
    engine_version: str

    entity_type: str
    raw_value: str
    canonical_value: str
    confidence: float
    decision: DecisionType
    explanation: List[Dict[str, str]]
    candidates: List[Candidate]

    extensions: Optional[Dict[str, object]] = None

    # ✅ Pydantic v2 正式設定
    model_config = {
        "exclude_none": True
    }