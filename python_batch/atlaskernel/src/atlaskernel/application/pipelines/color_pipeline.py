from typing import Dict, List, Tuple, Optional

from atlaskernel.services.normalize import normalize, normalize_key
from atlaskernel.services.similarity import similarity
from atlaskernel.adapters.assets_loader import load_assets
from atlaskernel.domain.candidate import Candidate
from atlaskernel.domain.result import AnalysisResult
from atlaskernel.version import VERSION


def _parse_canon_tsv_line(line: str) -> Optional[Tuple[str, str, List[str]]]:
    """
    colors_canon_v1.tsv:
      canonical<TAB>normalized_key<TAB>aliases(comma-separated)
    """
    s = line.strip()
    if not s or s.startswith("#"):
        return None
    parts = s.split("\t")
    if len(parts) < 1:
        return None

    canonical = parts[0].strip()
    normalized_key = parts[1].strip() if len(parts) >= 2 else normalize_key(canonical)
    aliases_raw = parts[2].strip() if len(parts) >= 3 else ""
    aliases = [a.strip() for a in aliases_raw.split(",") if a.strip()] if aliases_raw else []
    return canonical, normalized_key, aliases


def _load_canon_defs(ref: str) -> List[Tuple[str, str, List[str]]]:
    lines = load_assets(ref)
    out: List[Tuple[str, str, List[str]]] = []
    for line in lines:
        parsed = _parse_canon_tsv_line(line)
        if parsed:
            out.append(parsed)
    return out


def _build_alias_map_from_canon(canon_defs: List[Tuple[str, str, List[str]]]) -> Dict[str, str]:
    """
    Builds { normalize(alias): canonical } from canon TSV (SoT).
    Also maps canonical itself.
    """
    m: Dict[str, str] = {}
    for canonical, _key, aliases in canon_defs:
        # canonical itself
        m[normalize(canonical)] = canonical

        # aliases
        for a in aliases:
            m[normalize(a)] = canonical

    return m


def _load_alias_map(ref: str) -> Dict[str, str]:
    """
    colors_alias_v1.tsv:
      alias<TAB>canonical
    """
    lines = load_assets(ref)
    m: Dict[str, str] = {}
    for line in lines:
        s = line.strip()
        if not s or s.startswith("#"):
            continue
        if "\t" not in s:
            continue
        alias, canonical = s.split("\t", 1)
        a = normalize(alias)
        c = canonical.strip()
        if a:
            m[a] = c
    return m


def analyze_color(request, policy_engine):
    norm = normalize(request.raw_value)

    # ---- 0) load canon defs (SoT) ----
    canon_defs = _load_canon_defs("colors_canon_v1")  # reads .tsv via loader

    # ---- 1) alias -> canonical (fast path) ----
    # Prefer explicit alias file, fallback to derived alias map from canon defs.
    alias_map = _load_alias_map("colors_alias_v1")
    if not alias_map and canon_defs:
        alias_map = _build_alias_map_from_canon(canon_defs)

    alias_hit = alias_map.get(norm)

    candidates: List[Candidate] = []

    if alias_hit:
        # deterministic mapping
        candidates.append(Candidate(value=alias_hit, score=0.95))
        explanation = [
            {"rule": "alias_map", "detail": f"hit=true ({request.raw_value} -> {alias_hit})"},
        ]
    else:
        # ---- 2) fallback similarity over canonical names only ----
        canonicals = [c for (c, _k, _a) in canon_defs] if canon_defs else []

        # last resort fallback to old behavior if canon file missing
        if not canonicals:
            canonicals = load_assets(request.known_assets_ref or "colors_v1")

        for c in canonicals:
            score = similarity(norm, normalize(c))
            candidates.append(Candidate(value=c, score=score))

        if not candidates:
            raise RuntimeError("No color assets loaded.")

        candidates.sort(key=lambda c: c.score, reverse=True)
        explanation = [
            {"rule": "similarity_canonical", "detail": f"top={candidates[0].score}"},
        ]

    candidates.sort(key=lambda c: c.score, reverse=True)
    top = candidates[0]

    decision, reason, trace = policy_engine.evaluate(
        policy_engine.load("color"),
        {"score": top.score},
    )

    explanation.extend([
        {"rule": "policy", "detail": reason or "n/a"},
    ])

    extensions = {"policy_trace": trace}

    if decision in ("needs_review", "rejected"):
        extensions["escalation"] = {
            "action": "human_review",
            "queue": "entity_review.color",
        }

    return AnalysisResult(
        entity_type="color",
        raw_value=request.raw_value,
        canonical_value=top.value,   # ★ always canonical (e.g., ブルー)
        confidence=top.score,
        decision=decision,
        explanation=explanation,
        candidates=candidates[:5],
        engine_version=VERSION,
        extensions=extensions,
    )