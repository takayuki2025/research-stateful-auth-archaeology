from __future__ import annotations
from dataclasses import dataclass
from typing import Optional, Dict, Any


@dataclass(frozen=True)
class Decision:
    """
    Domain-level decision result.
    v2 以降の Policy / Rule / Escalation の共通出力。
    """

    decision: str               # auto_accept | human_review | rejected | escalated
    confidence: float
    rule_id: str
    reason: Optional[str] = None
    meta: Optional[Dict[str, Any]] = None