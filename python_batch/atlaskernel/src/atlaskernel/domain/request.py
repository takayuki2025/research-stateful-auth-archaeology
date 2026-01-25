from pydantic import BaseModel
from typing import Optional, Dict, Any

class AnalysisRequest(BaseModel):
    entity_type: str
    raw_value: str
    known_assets_ref: Optional[str] = None
    image_path: Optional[str] = None
    context: Dict[str, Any] = {}