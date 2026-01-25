from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from pydantic import BaseModel
from typing import Any, Dict, Optional

from atlaskernel.db.session import get_db
from atlaskernel.application.analyze_entity import analyze as analyze_entity

router = APIRouter(prefix="/v1", tags=["analyze"])

class AnalyzeRequest(BaseModel):
    project_id: str
    task_type: str = "entity_extract"
    raw_text: str
    mode: int = 2                       # 0..4（まずは2=local-only相当でOK）
    assets_ref: Optional[str] = None
    context: Dict[str, Any] = {}

class AnalyzeResponse(BaseModel):
    ok: bool
    result: Any
    trace: Dict[str, Any] = {}

@router.post("/analyze", response_model=AnalyzeResponse)
def analyze(payload: AnalyzeRequest, db: Session = Depends(get_db)):
    try:
        # まずは最小：entity_extract だけ対応
        # raw_text を request.raw_value として既存ロジックへ流し込む
        from atlaskernel.domain.request import AnalysisRequest as DomainReq
        req = DomainReq(
    entity_type="item",
    raw_value=payload.raw_text,
    known_assets_ref=payload.assets_ref,
    context=payload.context or {},   # ✅ これが必須
)

        results = analyze_entity(req)  # 既存 analyze() を利用（brand/condition/colorに分解）

        return AnalyzeResponse(
            ok=True,
            result={"items": results},
            trace={
                "project_id": payload.project_id,
                "task_type": payload.task_type,
                "mode": payload.mode,
            },
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"internal error: {e}")