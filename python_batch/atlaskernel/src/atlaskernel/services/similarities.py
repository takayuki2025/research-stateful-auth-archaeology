from typing import List, Tuple

def best_candidates(
    raw_value: str,
    known_values: List[str],
    top_k: int = 5,
) -> List[Tuple[str, float]]:
    """
    PoC用ダミー実装
    本番では embedding / fuzzy / multimodal に差し替える
    """
    results = []
    raw = raw_value.lower()

    for v in known_values:
        score = 1.0 if raw == v.lower() else 0.0
        results.append((v, score))

    return sorted(results, key=lambda x: x[1], reverse=True)[:top_k]