from typing import List

from atlaskernel.services.normalize import normalize
from atlaskernel.services.similarity import similarity
from atlaskernel.adapters.assets_loader import load_assets
from atlaskernel.domain.candidate import Candidate
from atlaskernel.domain.result import AnalysisResult
from atlaskernel.version import VERSION


def analyze_condition(request, policy_engine):
    norm = normalize(request.raw_value)
    assets = load_assets(request.known_assets_ref or "conditions_v1")

    candidates: List[Candidate] = []
    for a in assets:
        score = similarity(norm, normalize(a))
        candidates.append(Candidate(value=a, score=score))

    if not candidates:
        raise RuntimeError("No condition assets loaded.")

    candidates.sort(key=lambda c: c.score, reverse=True)
    top = candidates[0]

    decision, reason, trace = policy_engine.evaluate(
        policy_engine.load("condition"),
        {"score": top.score},
    )

    explanation = [
        {"rule": "similarity", "detail": f"top={top.score}"},
        {"rule": "policy", "detail": reason or "n/a"},
    ]

    extensions = {"policy_trace": trace}

    if decision in ("needs_review", "rejected"):
        extensions["escalation"] = {
            "action": "human_review",
            "queue": "entity_review.condition",
        }

    return AnalysisResult(
        entity_type="condition",
        raw_value=request.raw_value,
        canonical_value=top.value,
        confidence=top.score,
        decision=decision,
        explanation=explanation,
        candidates=candidates[:5],
        engine_version=VERSION,
        extensions=extensions,
    )