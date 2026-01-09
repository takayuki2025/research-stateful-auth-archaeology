from __future__ import annotations

from typing import Any, Dict, List, Optional
from pydantic import BaseModel, Field


class MediaRef(BaseModel):
    """
    画像/音/動画などの参照（将来 S3/GCS/DB 参照に拡張）。
    """
    kind: str  # "image" | "audio" | "video" | ...
    uri: str   # file path / URL / object key
    meta: Dict[str, Any] = Field(default_factory=dict)


class MultimodalRef(BaseModel):
    """
    1リクエストで複数メディアを渡せる形。
    """
    media: List[MediaRef] = Field(default_factory=list)

    def has_media(self) -> bool:
        return len(self.media) > 0