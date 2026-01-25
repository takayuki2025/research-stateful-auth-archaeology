from __future__ import annotations

from typing import Dict, List, Optional
from pathlib import Path

from atlaskernel.services.normalize import normalize

# ==========================================================
# assets path
# ==========================================================
BASE_DIR = Path(__file__).resolve().parent.parent
ASSETS_DIR = BASE_DIR / "assets"


def _load_terms_txt(filename: str) -> List[str]:
    path = ASSETS_DIR / filename
    seen = set()
    terms: List[str] = []
    with open(path, encoding="utf-8") as f:
        for line in f:
            t = normalize(line.strip())
            if t and t not in seen:
                seen.add(t)
                terms.append(t)
    return sorted(terms, key=len, reverse=True)


def _load_brand_alias_keys_tsv(filename: str = "brands_alias_v1.tsv") -> List[str]:
    """
    brands_alias_v1.tsv: alias<TAB>canonical
    alias のみを normalize して、最長一致で prefix 判定に使う
    """
    path = ASSETS_DIR / filename
    if not path.exists():
        return []

    keys: List[str] = []
    with open(path, encoding="utf-8") as f:
        for line in f:
            s = line.strip()
            if not s or s.startswith("#"):
                continue
            if "\t" not in s:
                continue
            alias, _canon = s.split("\t", 1)
            a = normalize(alias)
            if a:
                keys.append(a)

    return sorted(set(keys), key=len, reverse=True)


BRANDS = _load_terms_txt("brands_v1.txt")
CONDITIONS = _load_terms_txt("conditions_v1.txt")
COLORS = _load_terms_txt("colors_v1.txt")

BRAND_ALIAS_KEYS = _load_brand_alias_keys_tsv("brands_alias_v1.tsv")

# デバッグしたい時だけON（常時だとログが重い）
# print("[DEBUG] BRANDS =", BRANDS[:50])
# print("[DEBUG] CONDITIONS =", CONDITIONS[:50])
# print("[DEBUG] COLORS =", COLORS[:50])
# print("[DEBUG] BRAND_ALIAS_KEYS =", BRAND_ALIAS_KEYS[:50])


def _suffix_pick(s: str, terms: List[str]) -> str:
    for t in terms:
        if t and s.endswith(t):
            return t
    return ""


def _prefix_pick(s: str, terms: List[str]) -> str:
    for t in terms:
        if t and s.startswith(t):
            return t
    return ""


def split_entities(text: str, mode: str = "raw") -> Dict[str, str]:
    """
    mode:
      - attributes: brand_text 用（末尾から condition/color 抜いて残り=brand）
      - raw: raw_text 用（brandは “生成しない”。prefix一致できる場合だけbrandにする）
    """
    norm = normalize(text)

    result: Dict[str, str] = {
        "brand": "",
        "condition": "",
        "color": "",
        "rest": norm,
    }

    # ----------------------------------------------------------
    # 1) color / condition: suffix で抜く（マイクロソフト内クロ問題を防ぐ）
    # ----------------------------------------------------------
    c = _suffix_pick(result["rest"], COLORS)
    if c:
        result["color"] = c
        result["rest"] = result["rest"][: -len(c)].strip()

    cond = _suffix_pick(result["rest"], CONDITIONS)
    if cond:
        result["condition"] = cond
        result["rest"] = result["rest"][: -len(cond)].strip()

    # ----------------------------------------------------------
    # 2) attributes: 残り全部を brand（強制）
    # ----------------------------------------------------------
    if mode == "attributes":
        result["brand"] = result["rest"].strip()
        result["rest"] = ""
        return result

    # ----------------------------------------------------------
    # 3) raw: brand を “作らない”
    #    ただし prefix 一致できるなら brand だけ取る（誤爆防止）
    # ----------------------------------------------------------
    # 3-1) alias keys で prefix
    b = _prefix_pick(result["rest"], BRAND_ALIAS_KEYS)
    if not b:
        # 3-2) brands_v1.txt で prefix
        b = _prefix_pick(result["rest"], BRANDS)

    if b:
        result["brand"] = b
        result["rest"] = result["rest"][len(b):].strip()

    return result