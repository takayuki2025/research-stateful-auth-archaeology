from __future__ import annotations

from typing import Any, Dict, Optional

from atlaskernel.domain.context import Context
from atlaskernel.domain.multimodal import MultimodalRef
from atlaskernel.adapters.vision.image_context_adapter import ImageContextAdapter
from atlaskernel.adapters.audio.audio_context_adapter import AudioContextAdapter


class ContextBuilder:
    """
    入力payload（known_assets_ref 等）や multimodal 参照から Context を組み立てる。
    今は最小実装。将来、画像/音の特徴抽出をここに集約する。
    """

    def __init__(
        self,
        image_adapter: Optional[ImageContextAdapter] = None,
        audio_adapter: Optional[AudioContextAdapter] = None,
    ) -> None:
        self.image_adapter = image_adapter or ImageContextAdapter()
        self.audio_adapter = audio_adapter or AudioContextAdapter()

    def build(
        self,
        base: Optional[Dict[str, Any]] = None,
        multimodal: Optional[MultimodalRef] = None,
    ) -> Context:
        base = base or {}
        ctx = Context(
            locale=base.get("locale"),
            shop_id=base.get("shop_id"),
            categories=base.get("categories") or [],
            extra=base.get("extra") or {},
        )

        if multimodal and multimodal.has_media():
            # ここで vision/audio を抽出して ctx に詰める（現時点は stub）
            ctx.vision.update(self.image_adapter.extract(multimodal))
            ctx.audio.update(self.audio_adapter.extract(multimodal))

        return ctx