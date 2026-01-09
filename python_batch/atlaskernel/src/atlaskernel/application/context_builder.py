from typing import Any, Dict, Optional

from atlaskernel.domain.policy.policy_context import PolicyContext
from atlaskernel.adapters.vision.image_context_adapter import ImageContextAdapter

class ContextBuilder:
    def __init__(self, image_adapter: Optional[ImageContextAdapter] = None):
        self.image_adapter = image_adapter or ImageContextAdapter()

    def build(
        self,
        entity_type: str,
        source: str,
        text: Optional[Dict[str, Any]] = None,
        image_path: Optional[str] = None,
    ) -> PolicyContext:
        image_features = None
        if image_path:
            image_features = self.image_adapter.extract(image_path)

        return PolicyContext(
            entity_type=entity_type,
            source=source,
            text=text,
            image_features=image_features,
        )