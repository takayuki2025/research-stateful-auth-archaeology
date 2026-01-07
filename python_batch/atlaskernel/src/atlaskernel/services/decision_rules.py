from typing import Tuple, List, Dict
from atlaskernel.domain.decisions import AUTO_ACCEPT, NEEDS_REVIEW, REJECTED

def decide(score: float) -> Tuple[str, List[Dict[str, str]]]:
    explanation = []
    if score >= 0.92:
        explanation.append({"rule": "threshold", "detail": f"score {score} >= 0.92"})
        return AUTO_ACCEPT, explanation
    if score >= 0.75:
        explanation.append({"rule": "threshold", "detail": f"0.75 <= score {score} < 0.92"})
        return NEEDS_REVIEW, explanation
    explanation.append({"rule": "threshold", "detail": f"score {score} < 0.75"})
    return REJECTED, explanation