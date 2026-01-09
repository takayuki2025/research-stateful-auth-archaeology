from typing import List

from atlaskernel.application.context_builder import ContextBuilder
from atlaskernel.domain.policy.policy_input import PolicyInput, Candidate as PolicyCandidate
from atlaskernel.domain.request import AnalysisRequest
from atlaskernel.domain.result import AnalysisResult, Candidate
from atlaskernel.services.normalize import normalize
from atlaskernel.services.similarity import similarity
from atlaskernel.adapters.assets_loader import load_assets

def analyze_brand(req: AnalysisRequest, policy) -> AnalysisResult:
    norm = normalize(req.raw_value)
    assets = load_assets(req.known_assets_ref or "brands_v1")

    candidates: List[PolicyCandidate] = []
    for a in assets:
        score = similarity(norm, normalize(a))
        candidates.append(PolicyCandidate(value=a, score=float(score)))

    candidates.sort(key=lambda c: c.score, reverse=True)
    top_score = candidates[0].score if candidates else 0.0

    policy_input = PolicyInput(
        raw_value=req.raw_value,
        candidates=candidates,
        top_score=float(top_score),
    )

    context = ContextBuilder().build(
        entity_type="brand",
        source="ec_item",
        text={"title": req.raw_value},
        image_path=getattr(req, "image_path", None),
    )

    policy_result = policy.decide(policy_input, context)

    return AnalysisResult(
        entity_type="brand",
        raw_value=req.raw_value,
        canonical_value=policy_result.canonical_value,
        confidence=float(policy_result.confidence),
        decision=policy_result.decision.value,
        rule_id=policy_result.rule_id,
        candidates=[Candidate(value=c.value, score=float(c.score)) for c in candidates[:5]],
        explanation=[{
            "rule": policy_result.rule_id,
            "confidence": float(policy_result.confidence),
            "trace": policy_result.trace,
        }],
        extensions={
            "policy_trace": policy_result.trace,
            "policy_action": policy_result.action.value,
            "image_features": context.image_features,
        },
    )