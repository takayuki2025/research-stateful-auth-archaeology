from __future__ import annotations

from typing import Any, Dict
from atlaskernel.domain.multimodal import MultimodalRef


class AudioContextAdapter:
    """
    将来:
    - 音源分離・異音検知・話者推定・感情推定などを抽出して返す
    """

    def extract(self, mm: MultimodalRef) -> Dict[str, Any]:
        # stub
        return {}