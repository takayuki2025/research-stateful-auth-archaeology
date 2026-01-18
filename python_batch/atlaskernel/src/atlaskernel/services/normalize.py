import re
from typing import Dict, List, Optional
import unicodedata

# =========================
# Brand aliases (SoT: human curated)
# =========================
# NOTE:
# - keys are *human-friendly*, we normalize them into BRAND_ALIAS_MAP later.
BRAND_ALIASES_RAW = {
    # Rolex
    "rolax": "rolex",
    "ロレックス": "rolex",
    "ro lex": "rolex",

    # Apple
    "アップル": "apple",
    "あっぷる": "apple",
    "apple": "apple",
}

# =========================
# Kana utilities
# =========================
def hira_to_kata(s: str) -> str:
    """ひらがな(3041-3096) -> カタカナ(30A1-30F6)"""
    out = []
    for ch in s:
        code = ord(ch)
        if 0x3041 <= code <= 0x3096:
            out.append(chr(code + 0x60))
        else:
            out.append(ch)
    return "".join(out)

# =========================
# Core normalize (DB-grade key)
# =========================
_JP_KEEP = r"\u3040-\u30FF\u4E00-\u9FFF"  # hiragana/katakana/kanji
_RE_NON_KEEP = re.compile(rf"[^\w\s{_JP_KEEP}]")
_RE_SPACE = re.compile(r"\s+")

def normalize_key(text: str) -> str:
    """
    DB-grade normalize for entity keys:
    - NFKC normalize (全角/半角/互換統一)
    - lower
    - keep: word chars + spaces + Japanese ranges
    - punctuation -> space
    - collapse spaces
    - hiragana -> katakana (重要: あお == アオ)
    """
    if not text:
        return ""

    t = unicodedata.normalize("NFKC", text)
    t = t.strip().lower()

    # 記号を空白化（日本語/英数/空白を残す）
    t = _RE_NON_KEEP.sub(" ", t)
    t = _RE_SPACE.sub(" ", t).strip()

    # かな揺れ統一（色・状態で効く）
    t = hira_to_kata(t)

    # もう一度空白圧縮（変換で空白が変わるケース対策）
    t = _RE_SPACE.sub(" ", t).strip()
    return t

# =========================
# Build normalized brand alias map
# =========================
def _build_brand_alias_map(raw: Dict[str, str]) -> Dict[str, str]:
    m: Dict[str, str] = {}
    for k, v in raw.items():
        nk = normalize_key(k)
        if nk:
            m[nk] = v
    return m

BRAND_ALIAS_MAP = _build_brand_alias_map(BRAND_ALIASES_RAW)

# =========================
# Public normalize (entity-level)
# =========================
def normalize(text: str) -> str:
    """
    General entity normalize:
    - returns normalized key
    - applies brand alias replacement (only when it hits)
    """
    nk = normalize_key(text)
    if not nk:
        return ""

    # brand alias replacement (safe: only on hit)
    return BRAND_ALIAS_MAP.get(nk, nk)

# =========================
# document_term utilities
# =========================
def extract_aliases(text: str) -> List[str]:
    aliases: List[str] = []

    # 括弧内（日本語/英語）
    for m in re.findall(r"[（(](.*?)[）)]", text):
        m2 = m.strip()
        if m2:
            aliases.append(m2)

    # スラッシュ区切り
    if "/" in text:
        parts = [p.strip() for p in text.split("/") if p.strip()]
        if len(parts) >= 2:
            aliases.extend(parts[1:])

    # 重複除去（順序保持）
    seen = set()
    out: List[str] = []
    for a in aliases:
        if a not in seen:
            seen.add(a)
            out.append(a)
    return out


def normalize_document_term(text: str, context: Optional[Dict[str, str]] = None) -> str:
    """
    document_term 用は「意味を落としすぎない」方針で、かな変換は入れない（現状維持寄り）。
    必要ならここにも normalize_key を導入可能。
    """
    t = text.strip()

    # 括弧内は別名扱い
    t = re.sub(r"[（(].*?[）)]", "", t)

    # 記号整理
    t = re.sub(r"[_\-:]+", " ", t)

    # 日本語・英数・空白を残す
    t = _RE_NON_KEEP.sub(" ", t)

    # 空白圧縮
    t = _RE_SPACE.sub(" ", t).strip()
    return t


def normalize_text(text: str) -> str:
    """
    Generic text normalize (kept for compatibility).
    If you want unified behavior, you can call normalize_key() instead.
    """
    if not text:
        return ""
    t = unicodedata.normalize("NFKC", text)
    t = t.lower().strip()
    t = _RE_SPACE.sub(" ", t)
    return t