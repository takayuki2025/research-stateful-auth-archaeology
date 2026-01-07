import re
from typing import Dict, List

# =========================
# 汎用 normalize（brand 等）
# =========================
BRAND_ALIASES = {
    "rolax": "rolex",
    "ロレックス": "rolex",
    "ro lex": "rolex",
}

def normalize(text: str) -> str:
    if not text:
        return ""

    t = text.lower().strip()
    t = re.sub(r"[^\w\s\u3040-\u30FF\u4E00-\u9FFF]", " ", t)
    t = re.sub(r"\s+", " ", t)

    return BRAND_ALIASES.get(t, t)


# =========================
# document_term 専用
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


def normalize_document_term(text: str, context: Dict[str, str] | None = None) -> str:
    t = text.strip()

    # 括弧内は別名扱い
    t = re.sub(r"[（(].*?[）)]", "", t)

    # 記号整理
    t = re.sub(r"[_\-:]+", " ", t)

    # 日本語・英数・空白を残す
    t = re.sub(r"[^\w\s\u3040-\u30FF\u4E00-\u9FFF]", " ", t)

    # 空白圧縮
    t = re.sub(r"\s+", " ", t).strip()

    return t