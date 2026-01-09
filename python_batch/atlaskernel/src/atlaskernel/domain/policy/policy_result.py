from dataclasses import dataclass
from typing import Any, Dict, Optional
from .decision import Decision

class PolicyAction(str):
    ACCEPT = "accept"
    REVIEW = "review"

@dataclass
class PolicyResult:
    decision: Decision
    canonical_value: Optional[str]
    confidence: float
    rule_id: str
    trace: Dict[str, Any]
    action: PolicyAction