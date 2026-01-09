from dataclasses import dataclass
from typing import Any, Dict, Optional


@dataclass(frozen=True)
class PolicyContext:
    entity_type: str
    source: str                        # e.g. "ec_item"
    locale: Optional[str] = None

    # multimodal（将来拡張）
    image_features: Optional[Dict[str, Any]] = None
    audio_features: Optional[Dict[str, Any]] = None

    # text/context（ECでも即使う）
    text: Optional[Dict[str, Any]] = None