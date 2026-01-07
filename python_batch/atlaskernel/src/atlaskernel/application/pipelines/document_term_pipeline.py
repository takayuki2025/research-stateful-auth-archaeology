from typing import Dict, List, Tuple

from atlaskernel.domain.candidate import Candidate
from atlaskernel.domain.result import AnalysisResult
from atlaskernel.services.normalize import normalize_document_term, extract_aliases
from atlaskernel.services.similarity import similarity
from atlaskernel.services.decision_rules import decide
from atlaskernel.adapters.assets_loader import load_assets
from atlaskernel.version import VERSION


def analyze_document_term(request) -> AnalysisResult:
    raw = request.raw_value
    context: Dict[str, str] = getattr(request, "context", {}) or {}

    # 1) 別名抽出（括弧・スラッシュ等）
    aliases = extract_aliases(raw)

    # 2) canonical 候補（まずは「主表記」を正規化）
    canonical = normalize_document_term(raw, context=context)

    # 3) 既知用語辞書があれば、最も近い canonical を当てる（任意）
    assets_ref = request.known_assets_ref or ""
    assets: List[str] = load_assets(assets_ref) if assets_ref else []

    candidates: List[Candidate] = []
    explanation: List[Dict[str, str]] = []

    if assets:
        for a in assets:
            score = similarity(canonical, normalize_document_term(a, context=context))
            candidates.append(Candidate(value=a, score=score))
        candidates.sort(key=lambda c: c.score, reverse=True)

        top = candidates[0]
        decision, expl2 = decide(top.score)
        explanation.append({"rule": "dictionary_similarity", "detail": f"ref={assets_ref} top={top.score}"})
        explanation.extend(expl2)

        canonical_value = top.value
        confidence = top.score
        top5 = candidates[:5]
    else:
        # 辞書が無い場合は「正規化結果を canonical」として返す（MVP）
        decision, expl2 = decide(0.90)  # 辞書なしのため仮の信頼度（将来改善）
        explanation.append({"rule": "normalize_only", "detail": "no known_assets_ref provided"})
        explanation.extend(expl2)

        canonical_value = canonical
        confidence = 0.90
        top5 = [Candidate(value=canonical_value, score=confidence)]

    # 4) aliases を explanation に残す（将来KG/多言語の基礎）
    if aliases:
        explanation.insert(0, {"rule": "aliases_extracted", "detail": ", ".join(aliases)})

    return AnalysisResult(
        entity_type=request.entity_type,
        raw_value=request.raw_value,
        canonical_value=canonical_value,
        confidence=confidence,
        decision=decision,
        explanation=explanation,
        candidates=top5,
        engine_version=VERSION
    )