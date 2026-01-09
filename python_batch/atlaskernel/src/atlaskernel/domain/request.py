from pydantic import BaseModel
from typing import Optional


class AnalysisRequest(BaseModel):
    entity_type: str
    raw_value: str
    known_assets_ref: Optional[str] = None
    image_path: Optional[str] = None