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


def _decode_and_verify(req: PdfTextExtractRequest) -> bytes:
    try:
        pdf_bytes = base64.b64decode(req.content_b64)
    except Exception:
        raise HTTPException(status_code=400, detail="content_b64 decode failed")

    sha = hashlib.sha256(pdf_bytes).hexdigest()
    if sha != req.content_sha256:
        raise HTTPException(status_code=400, detail="content_sha256 mismatch")

    return pdf_bytes


def _normalize_text(text: str) -> str:
    return (
        (text or "")
        .replace("\r\n", "\n")
        .replace("\r", "\n")
        .strip()
    )


@router.post("/v1/extract/pdf_text", response_model=PdfTextExtractResponse)
def extract_pdf_text(req: PdfTextExtractRequest) -> PdfTextExtractResponse:
    pdf_bytes = _decode_and_verify(req)

    try:
        text = pdf_extract_text(io.BytesIO(pdf_bytes)) or ""
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"pdf extract failed: {e}")

    text = _normalize_text(text)

    meta: dict[str, Any] = {
        "method": "pdfminer.six",
        "length": len(text),
        "source_url": req.source_url,
    }

    # v4.2へ繋ぐための「OCR推奨」フラグ
    meta["ocr_recommended"] = len(text) < int(req.options.get("min_length_for_no_ocr", 200))

    return PdfTextExtractResponse(text=text, meta=meta)


# =========================
# v4.2: OCR fallback endpoint
# =========================

class PdfOcrExtractRequest(BaseModel):
    content_b64: str
    content_sha256: str
    source_url: Optional[str] = None
    options: dict[str, Any] = {}


class PdfOcrExtractResponse(BaseModel):
    text: str
    meta: dict[str, Any]


@router.post("/v1/extract/pdf_ocr", response_model=PdfOcrExtractResponse)
def extract_pdf_ocr(req: PdfOcrExtractRequest) -> PdfOcrExtractResponse:
    pdf_bytes = _decode_and_verify(PdfTextExtractRequest(**req.model_dump()))

    # options
    lang = (req.options.get("language") or "jpn+eng").strip()
    dpi = int(req.options.get("dpi") or 200)
    max_pages = int(req.options.get("max_pages") or 10)

    try:
        # pdf -> images
        from pdf2image import convert_from_bytes
        images = convert_from_bytes(pdf_bytes, dpi=dpi)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"pdf->image failed: {e}")

    if not images:
        return PdfOcrExtractResponse(text="", meta={
            "method": "tesseract",
            "language": lang,
            "pages": 0,
            "length": 0,
            "source_url": req.source_url,
        })

    # limit pages (safety)
    images = images[:max_pages]

    try:
        import pytesseract
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"pytesseract import failed: {e}")

    texts: list[str] = []
    confs: list[float] = []

    for img in images:
        # text
        try:
            t = pytesseract.image_to_string(img, lang=lang) or ""
            texts.append(t)
        except Exception as e:
            raise HTTPException(status_code=500, detail=f"ocr text failed: {e}")

        # confidence (optional)
        try:
            data = pytesseract.image_to_data(img, lang=lang, output_type=pytesseract.Output.DICT)
            for c in data.get("conf", []) or []:
                try:
                    v = float(c)
                    if v >= 0:
                        confs.append(v)
                except Exception:
                    pass
        except Exception:
            # ignore confidence errors
            pass

    text = _normalize_text("\n\n".join(texts))

    avg_conf = (sum(confs) / len(confs)) if confs else None

    meta: dict[str, Any] = {
        "method": "tesseract",
        "language": lang,
        "dpi": dpi,
        "pages": len(images),
        "length": len(text),
        "avg_confidence": avg_conf,
        "source_url": req.source_url,
        "ocr_recommended": False,  # OCRを実施した時点でfalse
    }

    return PdfOcrExtractResponse(text=text, meta=meta)