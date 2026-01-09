from pydantic import BaseModel
from typing import Optional, List, Dict, Any

class Candidate(BaseModel):
    value: str
    score: float

class AnalysisResult(BaseModel):
    entity_type: str
    raw_value: str
    canonical_value: Optional[str]
    confidence: float
    decision: str
    rule_id: str
    candidates: List[Candidate]
    explanation: List[Dict[str, Any]]
    extensions: Dict[str, Any]
