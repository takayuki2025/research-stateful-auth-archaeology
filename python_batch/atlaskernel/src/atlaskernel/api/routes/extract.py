from __future__ import annotations

from typing import Any, Optional

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel

from atlaskernel.application.extract.ocr_router import (
    OcrRouter,
    decode_and_verify,
    parse_options,
    OcrOptions,
    ExtractResult,
)

router = APIRouter(tags=["extract"])


# =========================
# Request / Response Models
# =========================

class PdfExtractBaseRequest(BaseModel):
    content_b64: str
    content_sha256: str
    source_url: Optional[str] = None
    options: dict[str, Any] = {}


class PdfExtractResponse(BaseModel):
    text: str
    meta: dict[str, Any]


# =========================
# Internal helpers
# =========================

def _to_http_error(e: Exception) -> HTTPException:
    """
    Convert internal errors to HTTPException.
    We keep messages short to avoid leaking internals; full detail should be logged if needed.
    """
    msg = str(e) or e.__class__.__name__
    if "content_b64 decode failed" in msg or "content_sha256 mismatch" in msg:
        return HTTPException(status_code=400, detail=msg)
    return HTTPException(status_code=500, detail=msg)


def _run_router(
    *,
    pdf_bytes: bytes,
    source_url: Optional[str],
    opt: OcrOptions,
    mode_override: Optional[str] = None,   # "force_ocr" | None
) -> ExtractResult:
    """
    Single entry to Router, allowing endpoint-specific mode override.
    """
    router_impl = OcrRouter()

    if mode_override is not None:
        # Create a new options with overridden mode (dataclass is frozen, so replace via model_dump-like construction)
        opt = OcrOptions(
            engine=opt.engine,
            mode=mode_override,  # type: ignore[arg-type]
            lang=opt.lang,
            dpi=opt.dpi,
            max_pages=opt.max_pages,
            min_length_for_no_ocr=opt.min_length_for_no_ocr,
            min_confidence=opt.min_confidence,
            budget=opt.budget,
        )

    return router_impl.route(pdf_bytes, source_url, opt)


# =========================
# Endpoints (v4.2)
# =========================

@router.post("/v1/extract/pdf_text", response_model=PdfExtractResponse)
def extract_pdf_text(req: PdfExtractBaseRequest) -> PdfExtractResponse:
    """
    v4.2: pdf_text endpoint (cheap)
    - Always runs Router.extract_pdf_text (no OCR)
    - Returns meta.ocr_recommended that Laravel can use for conditional fallback
    """
    try:
        pdf_bytes = decode_and_verify(req.content_b64, req.content_sha256)
        opt = parse_options(req.options or {})
        r = OcrRouter().extract_pdf_text(pdf_bytes, req.source_url, opt)
        # Ensure audit fixed fields exist (these are helpful even for pdf_text-only)
        meta = dict(r.meta)
        meta.setdefault("engine_requested", opt.engine)
        meta.setdefault("mode", opt.mode)
        meta.setdefault("lang", opt.lang)
        meta.setdefault("min_confidence", opt.min_confidence)
        meta.setdefault("budget", {"max_ms": opt.budget.max_ms, "max_cost_usd": opt.budget.max_cost_usd})
        meta.setdefault("pipeline", "pdf_text_only")
        meta.setdefault("fallback_chain", ["pdf_text"])
        # decision is optional here; Laravel will decide whether to OCR or not
        return PdfExtractResponse(text=r.text, meta=meta)
    except Exception as e:
        raise _to_http_error(e)


@router.post("/v1/extract/pdf_ocr", response_model=PdfExtractResponse)
def extract_pdf_ocr(req: PdfExtractBaseRequest) -> PdfExtractResponse:
    """
    v4.2: OCR endpoint (explicit)
    - Treat as "force_ocr" at API boundary
    - Keeps meta.pipeline/pdf_ocr_only etc. stable
    """
    try:
        pdf_bytes = decode_and_verify(req.content_b64, req.content_sha256)
        opt = parse_options(req.options or {})
        r = _run_router(pdf_bytes=pdf_bytes, source_url=req.source_url, opt=opt, mode_override="force_ocr")

        meta = dict(r.meta)
        # Hard audit guarantees (even if Router changes later)
        meta.setdefault("pipeline", "pdf_ocr_only")
        meta.setdefault("fallback_chain", ["pdf_ocr"])
        meta.setdefault("engine_requested", opt.engine)
        meta.setdefault("mode", "force_ocr")
        meta.setdefault("lang", opt.lang)
        meta.setdefault("min_confidence", opt.min_confidence)
        meta.setdefault("budget", {"max_ms": opt.budget.max_ms, "max_cost_usd": opt.budget.max_cost_usd})

        return PdfExtractResponse(text=r.text, meta=meta)
    except Exception as e:
        raise _to_http_error(e)


@router.post("/v1/extract/pdf_extract", response_model=PdfExtractResponse)
def extract_pdf_extract(req: PdfExtractBaseRequest) -> PdfExtractResponse:
    """
    v4.2 (recommended): Auto Router endpoint
    - Runs Router.route with opt.mode
    - This is the clean "engine router" entrypoint for future (PaddleOCR/DocTR/Textract).
    - Laravel can call this single endpoint if you want to simplify client logic later.
    """
    try:
        pdf_bytes = decode_and_verify(req.content_b64, req.content_sha256)
        opt = parse_options(req.options or {})
        r = _run_router(pdf_bytes=pdf_bytes, source_url=req.source_url, opt=opt, mode_override=None)
        return PdfExtractResponse(text=r.text, meta=dict(r.meta))
    except Exception as e:
        raise _to_http_error(e)