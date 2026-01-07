from fastapi import FastAPI
from pydantic import BaseModel
from atlaskernel.application.analyze_entity import analyze

app = FastAPI(title="AtlasKernel API")

class AnalyzeRequest(BaseModel):
    entity_type: str
    raw_value: str
    known_assets_ref: str | None = None

@app.post("/analyze")
def analyze_entity(req: AnalyzeRequest):
    result = analyze(req)
    return result.model_dump(exclude_none=True)