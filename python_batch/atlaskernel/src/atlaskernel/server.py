from fastapi import FastAPI
from sqlalchemy import text
from atlaskernel.db.session import engine
from atlaskernel.api.routes.reviews import router as review_router

app = FastAPI(title="AtlasKernel API")

# API routes
app.include_router(review_router)

# Health check
@app.get("/health")
def health():
    try:
        with engine.connect() as conn:
            conn.execute(text("SELECT 1"))
        return {"status": "ok", "db": "ok"}
    except Exception as e:
        return {"status": "ng", "error": str(e)}