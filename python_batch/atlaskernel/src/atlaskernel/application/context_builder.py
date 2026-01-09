from atlaskernel.domain.policy.policy_context import PolicyContext
from atlaskernel.adapters.vision.image_context_adapter import ImageContextAdapter

class ContextBuilder:

    def build(
        self,
        entity_type: str,
        source: str,
        text: dict | None = None,
        image_path: str | None = None,
    ) -> PolicyContext:

        image_features = None
        if image_path:
            image_features = ImageContextAdapter().extract(image_path)

        return PolicyContext(
            entity_type=entity_type,
            source=source,
            text=text,                 # ★ 修正点
            image_features=image_features,
        )