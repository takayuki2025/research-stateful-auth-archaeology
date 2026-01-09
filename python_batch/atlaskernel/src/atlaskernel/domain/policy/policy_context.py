from dataclasses import dataclass
from typing import Any, Dict, Optional

@dataclass
class PolicyContext:
    entity_type: str
    source: str
    locale: Optional[str] = None

    # ここを統一：ContextBuilder は text=... を使う
    text: Optional[Dict[str, Any]] = None

    # multimodal
    image_features: Optional[Dict[str, Any]] = None
    audio_features: Optional[Dict[str, Any]] = None