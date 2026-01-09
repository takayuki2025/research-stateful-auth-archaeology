from dataclasses import dataclass
from typing import List


@dataclass(frozen=True)
class Candidate:
    value: str
    score: float


@dataclass(frozen=True)
class PolicyInput:
    raw_value: str
    candidates: List[Candidate]
    top_score: float