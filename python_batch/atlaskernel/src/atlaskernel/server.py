from fastapi import FastAPI
from sqlalchemy import text

from atlaskernel.db.session import engine
from atlaskernel.api.routes.reviews import router as review_router
from atlaskernel.api.routes.analyze import router as analyze_router
from atlaskernel.api.routes.extract import router as extract_router

app = FastAPI(title="AtlasKernel API", version="0.3.1")

# API routes
app.include_router(review_router)    # /v1/reviews/...
app.include_router(analyze_router)   # /v1/analyze ✅
app.include_router(extract_router)

@app.get("/health")
def health():
    try:
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
        return {"status": "ok", "db": "ok"}
    except Exception as e:
        return {"status": "ng", "error": str(e)}