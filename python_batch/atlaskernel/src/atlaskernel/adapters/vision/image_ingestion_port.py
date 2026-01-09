from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import Any, Dict, Optional


@dataclass(frozen=True)
class ImageIngestionInput:
    image_bytes: bytes
    mime_type: Optional[str] = None


@dataclass(frozen=True)
class ImageIngestionOutput:
    features: Dict[str, Any]


class ImageIngestionPort(ABC):
    @abstractmethod
    def extract(self, inp: ImageIngestionInput) -> ImageIngestionOutput:
        raise NotImplementedError