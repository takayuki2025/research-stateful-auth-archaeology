from fastapi import FastAPI
from pydantic import BaseModel
from typing import List

from atlaskernel.application.analyze_entity import analyze
from atlaskernel.domain.request import AnalysisRequest

app = FastAPI(title="AtlasKernel API")


class AnalyzeRequest(BaseModel):
    entity_type: str
    raw_value: str
    known_assets_ref: str | None = None


@app.post("/analyze")
def analyze_entity(req: AnalyzeRequest):
    results = analyze(
        AnalysisRequest(
            entity_type=req.entity_type,
            raw_value=req.raw_value,
            known_assets_ref=req.known_assets_ref,
        )
    )

    return {
        "engine": {
            "name": "AtlasKernel",
            "version": "0.1.0",
        },
        "results": [r.model_dump(exclude_none=True) for r in results],
    }
