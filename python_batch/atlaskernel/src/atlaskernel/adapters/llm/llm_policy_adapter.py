from __future__ import annotations

from typing import Any, Dict, Optional
from atlaskernel.domain.context import Context


class LlmPolicyAdapter:
    """
    将来:
    - “文脈理解” を LLM/VLM に委譲して policy の補助情報（例: intent, scene, risk）を返す
    """
    def enrich(self, ctx: Context) -> Optional[Dict[str, Any]]:
        return None