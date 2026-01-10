from fastapi import FastAPI
from atlaskernel.api.routes.reviews import router as review_router

app = FastAPI(title="AtlasKernel API")
app.include_router(review_router)