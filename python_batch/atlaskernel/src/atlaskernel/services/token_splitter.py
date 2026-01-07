from typing import Dict, List
from pathlib import Path
from atlaskernel.services.normalize import normalize

# =========================
# assets の絶対パス解決
# =========================
BASE_DIR = Path(__file__).resolve().parent.parent
ASSETS_DIR = BASE_DIR / "assets"


def load_terms(filename: str) -> List[str]:
    path = ASSETS_DIR / filename
    seen = set()
    terms = []

    with open(path, encoding="utf-8") as f:
        for line in f:
            t = normalize(line.strip())
            if t and t not in seen:
                seen.add(t)
                terms.append(t)

    return sorted(terms, key=len, reverse=True)


# =========================
# assets 読み込み
# =========================
BRANDS = load_terms("brands_v1.txt")
CONDITIONS = load_terms("conditions_v1.txt")
COLORS = load_terms("colors_v1.txt")

# ★ デバッグ（最初は必ず入れる）
print("[DEBUG] BRANDS =", BRANDS)
print("[DEBUG] CONDITIONS =", CONDITIONS)
print("[DEBUG] COLORS =", COLORS)


# =========================
# token split
# =========================
def split_entities(text: str) -> Dict[str, str]:
    norm = normalize(text)

    result = {
        "brand": "",
        "condition": "",
        "color": "",
        "rest": norm,
    }

    for term in BRANDS:
        if term and term in result["rest"]:
            result["brand"] = term
            result["rest"] = result["rest"].replace(term, "", 1)
            break

    for term in CONDITIONS:
        if term and term in result["rest"]:
            result["condition"] = term
            result["rest"] = result["rest"].replace(term, "", 1)
            break

    for term in COLORS:
        if term and term in result["rest"]:
            result["color"] = term
            result["rest"] = result["rest"].replace(term, "", 1)
            break

    return result