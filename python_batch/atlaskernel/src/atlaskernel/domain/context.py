from __future__ import annotations

from typing import Any, Dict, List, Optional
from pydantic import BaseModel, Field


class Context(BaseModel):
    """
    PolicyEngine v2 が参照する “文脈”。
    - EC用途では: category / locale / shop_id / user intent など
    - 将来: 画像特徴量・音特徴量・セッション情報などを自然に追加できる
    """
    locale: Optional[str] = None
    shop_id: Optional[str] = None
    categories: List[str] = Field(default_factory=list)

    # 将来の multimodal feature（まずは dict で十分）
    vision: Dict[str, Any] = Field(default_factory=dict)
    audio: Dict[str, Any] = Field(default_factory=dict)

    # 任意拡張
    extra: Dict[str, Any] = Field(default_factory=dict)