from __future__ import annotations

import base64
import hashlib
import io
from typing import Any, Optional

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel

from pdfminer.high_level import extract_text as pdf_extract_text


router = APIRouter(tags=["extract"])


class PdfTextExtractRequest(BaseModel):
    content_b64: str
    content_sha256: str
    source_url: Optional[str] = None
    options: dict[str, Any] = {}


class PdfTextExtractResponse(BaseModel):
    text: str
    meta: dict[str, Any]


@router.post("/v1/extract/pdf_text", response_model=PdfTextExtractResponse)
def extract_pdf_text(req: PdfTextExtractRequest) -> PdfTextExtractResponse:
    # 1) decode bytes
    try:
        pdf_bytes = base64.b64decode(req.content_b64)
    except Exception:
        raise HTTPException(status_code=400, detail="content_b64 decode failed")

    # 2) sha256 verify
    sha = hashlib.sha256(pdf_bytes).hexdigest()
    if sha != req.content_sha256:
        raise HTTPException(status_code=400, detail="content_sha256 mismatch")

    # 3) extract text (pdfminer)
    try:
        text = pdf_extract_text(io.BytesIO(pdf_bytes)) or ""
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"pdf extract failed: {e}")

    # 4) minimal normalize (v4.1)
    text = (
        text.replace("\r\n", "\n")
            .replace("\r", "\n")
            .strip()
    )

    meta = {
        "method": "pdfminer.six",
        "length": len(text),
        "source_url": req.source_url,
    }

    # v4.2の条件付きOCRに繋ぐためのフラグ（MVP）
    # 文字がほぼ取れていない場合、OCR推奨
    meta["ocr_recommended"] = len(text) < 200

    return PdfTextExtractResponse(text=text, meta=meta)