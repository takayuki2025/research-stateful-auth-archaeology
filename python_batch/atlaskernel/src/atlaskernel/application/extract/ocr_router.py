from __future__ import annotations

import base64
import hashlib
import io
import time
from dataclasses import dataclass
from typing import Any, Optional, Literal

from pdfminer.high_level import extract_text as pdf_extract_text

Engine = Literal["auto", "tesseract", "paddleocr", "doctr", "textract", "document_ai"]
Mode = Literal["auto", "force_ocr"]


@dataclass(frozen=True)
class Budget:
    max_ms: int = 3000
    max_cost_usd: float = 0.0  # local engines are 0


@dataclass(frozen=True)
class OcrOptions:
    engine: Engine = "auto"
    mode: Mode = "auto"
    lang: str = "jpn"           # allow "jpn+eng"
    dpi: int = 200
    max_pages: int = 3
    min_length_for_no_ocr: int = 200
    min_confidence: float = 70.0
    budget: Budget = Budget()


@dataclass(frozen=True)
class ExtractResult:
    text: str
    meta: dict[str, Any]


class OcrRouter:
    """
    v4.2 Engine Router (final):
    - pdf_text: pdfminer
    - pdf_ocr: tesseract (baseline)
    - route: auto router + budget/confidence gate (audit first)
    """

    # -------- Core primitives --------

    def extract_pdf_text(self, pdf_bytes: bytes, source_url: Optional[str], opt: OcrOptions) -> ExtractResult:
        t0 = time.perf_counter()
        text = ""
        err = None

        try:
            text = pdf_extract_text(io.BytesIO(pdf_bytes)) or ""
        except Exception as e:
            err = str(e)

        text = self._normalize(text)
        elapsed_ms = int((time.perf_counter() - t0) * 1000)

        meta: dict[str, Any] = {
            "method": "pdfminer.six",
            "length": len(text),
            "source_url": source_url,
            "elapsed_ms": elapsed_ms,
            "cost_usd": 0.0,
            "ocr_recommended": len(text) < opt.min_length_for_no_ocr,
            "min_length_for_no_ocr": opt.min_length_for_no_ocr,
        }
        if err:
            meta["pdf_text_error"] = err

        return ExtractResult(text=text, meta=meta)

    def extract_pdf_ocr(self, pdf_bytes: bytes, source_url: Optional[str], opt: OcrOptions, engine_selected: str) -> ExtractResult:
        """
        Current implementation uses Tesseract only (baseline).
        """
        t0 = time.perf_counter()

        # pdf -> images
        try:
            from pdf2image import convert_from_bytes
            images = convert_from_bytes(pdf_bytes, dpi=opt.dpi)
        except Exception as e:
            return ExtractResult(
                text="",
                meta={
                    "method": "tesseract",
                    "engine_selected": engine_selected,
                    "lang": opt.lang,
                    "dpi": opt.dpi,
                    "pages": 0,
                    "length": 0,
                    "source_url": source_url,
                    "elapsed_ms": int((time.perf_counter() - t0) * 1000),
                    "cost_usd": 0.0,
                    "ocr_error": f"pdf->image failed: {e}",
                    "avg_confidence": None,
                },
            )

        if not images:
            return ExtractResult(
                text="",
                meta={
                    "method": "tesseract",
                    "engine_selected": engine_selected,
                    "lang": opt.lang,
                    "dpi": opt.dpi,
                    "pages": 0,
                    "length": 0,
                    "source_url": source_url,
                    "elapsed_ms": int((time.perf_counter() - t0) * 1000),
                    "cost_usd": 0.0,
                    "avg_confidence": None,
                },
            )

        images = images[: max(1, opt.max_pages)]

        try:
            import pytesseract
        except Exception as e:
            return ExtractResult(
                text="",
                meta={
                    "method": "tesseract",
                    "engine_selected": engine_selected,
                    "lang": opt.lang,
                    "dpi": opt.dpi,
                    "pages": len(images),
                    "length": 0,
                    "source_url": source_url,
                    "elapsed_ms": int((time.perf_counter() - t0) * 1000),
                    "cost_usd": 0.0,
                    "ocr_error": f"pytesseract import failed: {e}",
                    "avg_confidence": None,
                },
            )

        texts: list[str] = []
        confs: list[float] = []

        for img in images:
            try:
                texts.append(pytesseract.image_to_string(img, lang=opt.lang) or "")
            except Exception as e:
                return ExtractResult(
                    text="",
                    meta={
                        "method": "tesseract",
                        "engine_selected": engine_selected,
                        "lang": opt.lang,
                        "dpi": opt.dpi,
                        "pages": len(images),
                        "length": 0,
                        "source_url": source_url,
                        "elapsed_ms": int((time.perf_counter() - t0) * 1000),
                        "cost_usd": 0.0,
                        "ocr_error": f"ocr text failed: {e}",
                        "avg_confidence": None,
                    },
                )

            # confidence (optional)
            try:
                data = pytesseract.image_to_data(img, lang=opt.lang, output_type=pytesseract.Output.DICT)
                for c in data.get("conf", []) or []:
                    try:
                        v = float(c)
                        if v >= 0:
                            confs.append(v)
                    except Exception:
                        pass
            except Exception:
                pass

        text = self._normalize("\n\n".join(texts))
        avg_conf = (sum(confs) / len(confs)) if confs else None
        elapsed_ms = int((time.perf_counter() - t0) * 1000)

        meta: dict[str, Any] = {
            "method": "tesseract",
            "engine_selected": engine_selected,
            "lang": opt.lang,
            "dpi": opt.dpi,
            "pages": len(images),
            "length": len(text),
            "avg_confidence": avg_conf,
            "source_url": source_url,
            "elapsed_ms": elapsed_ms,
            "cost_usd": 0.0,
            "ocr_recommended": False,
        }

        return ExtractResult(text=text, meta=meta)

    # -------- Router (v4.2 final) --------

    def route(self, pdf_bytes: bytes, source_url: Optional[str], opt: OcrOptions) -> ExtractResult:
        """
        v4.2 final contract (audit-first):
          meta.pipeline: pdf_text_only | pdf_text_then_ocr | pdf_ocr_only
          meta.fallback_chain: [pdf_text] / [pdf_text,pdf_ocr] / [pdf_ocr]
          meta.engine_requested: opt.engine
          meta.engine_selected: resolved engine
          meta.decision: accept | review_required
          meta.decision_reason
          meta.budget_exceeded
          meta.elapsed_ms_total
        """
        engine_requested = opt.engine

        # Resolve engine (MVP: only tesseract available)
        engine_selected = "tesseract" if engine_requested in ("auto", "tesseract") else "tesseract"

        # force_ocr => OCR only
        if opt.mode == "force_ocr":
            r = self.extract_pdf_ocr(pdf_bytes, source_url, opt, engine_selected)
            meta = dict(r.meta)
            meta.update(self._audit_fixed(opt, engine_requested, engine_selected))
            meta["pipeline"] = "pdf_ocr_only"
            meta["fallback_chain"] = ["pdf_ocr"]

            # decision gate
            return self._finalize(meta, r.text)

        # auto => try pdf_text first
        t = self.extract_pdf_text(pdf_bytes, source_url, opt)
        meta_t = dict(t.meta)
        meta_t.update(self._audit_fixed(opt, engine_requested, engine_selected))

        if meta_t.get("ocr_recommended") is True:
            o = self.extract_pdf_ocr(pdf_bytes, source_url, opt, engine_selected)
            meta = dict(meta_t)
            meta.update(o.meta)  # OCR meta wins
            meta["pipeline"] = "pdf_text_then_ocr"
            meta["fallback_chain"] = ["pdf_text", "pdf_ocr"]

            # choose OCR text if non-empty
            text = o.text.strip() and o.text or t.text
            if not o.text.strip():
                meta["ocr_error"] = meta.get("ocr_error") or "ocr returned empty text"

            return self._finalize(meta, text)

        # accept pdf_text
        meta_t["pipeline"] = "pdf_text_only"
        meta_t["fallback_chain"] = ["pdf_text"]
        return self._finalize(meta_t, t.text)

    # -------- Helpers --------

    def _audit_fixed(self, opt: OcrOptions, engine_requested: str, engine_selected: str) -> dict[str, Any]:
        return {
            "engine_requested": engine_requested,
            "engine_selected": engine_selected,
            "mode": opt.mode,
            "lang": opt.lang,
            "min_confidence": opt.min_confidence,
            "budget": {"max_ms": opt.budget.max_ms, "max_cost_usd": opt.budget.max_cost_usd},
        }

    def _finalize(self, meta: dict[str, Any], text: str) -> ExtractResult:
        # elapsed total (best-effort: if both text+ocr happened, caller merged meta, so we only have last elapsed_ms.
        # keep "elapsed_ms_total" as that value for now; later we can sum explicitly.
        elapsed_ms = meta.get("elapsed_ms")
        meta["elapsed_ms_total"] = elapsed_ms if isinstance(elapsed_ms, int) else None

        # budget gate
        max_ms = int(meta.get("budget", {}).get("max_ms") or 3000)
        budget_exceeded = isinstance(elapsed_ms, int) and elapsed_ms > max_ms
        meta["budget_exceeded"] = budget_exceeded

        # confidence gate (only if avg_confidence exists)
        avg_conf = meta.get("avg_confidence")
        min_conf = float(meta.get("min_confidence") or 70.0)

        if budget_exceeded:
            meta["decision"] = "review_required"
            meta["decision_reason"] = "budget_exceeded"
        elif avg_conf is None:
            meta["decision"] = "review_required"
            meta["decision_reason"] = "avg_confidence_missing"
        else:
            try:
                v = float(avg_conf)
                if v >= min_conf:
                    meta["decision"] = "accept"
                    meta["decision_reason"] = "confidence_ok"
                else:
                    meta["decision"] = "review_required"
                    meta["decision_reason"] = "confidence_below_threshold"
            except Exception:
                meta["decision"] = "review_required"
                meta["decision_reason"] = "avg_confidence_parse_failed"

        return ExtractResult(text=text, meta=meta)

    def _normalize(self, text: str) -> str:
        return (text or "").replace("\r\n", "\n").replace("\r", "\n").strip()


def decode_and_verify(content_b64: str, content_sha256: str) -> bytes:
    try:
        pdf_bytes = base64.b64decode(content_b64)
    except Exception:
        raise ValueError("content_b64 decode failed")

    sha = hashlib.sha256(pdf_bytes).hexdigest()
    if sha != content_sha256:
        raise ValueError("content_sha256 mismatch")

    return pdf_bytes


def parse_options(options: dict[str, Any]) -> OcrOptions:
    engine = str(options.get("engine") or "auto")
    mode = str(options.get("mode") or "auto")
    lang = str(options.get("lang") or "jpn")
    dpi = int(options.get("dpi") or 200)
    max_pages = int(options.get("max_pages") or 3)
    min_len = int(options.get("min_length_for_no_ocr") or 200)
    min_conf = float(options.get("min_confidence") or 70.0)

    budget_obj = options.get("budget") or {}
    if not isinstance(budget_obj, dict):
        budget_obj = {}

    budget = Budget(
        max_ms=int(budget_obj.get("max_ms") or 3000),
        max_cost_usd=float(budget_obj.get("max_cost_usd") or 0.0),
    )

    # normalize enums
    if engine not in {"auto", "tesseract", "paddleocr", "doctr", "textract", "document_ai"}:
        engine = "auto"
    if mode not in {"auto", "force_ocr"}:
        mode = "auto"

    return OcrOptions(
        engine=engine, mode=mode, lang=lang, dpi=dpi, max_pages=max_pages,
        min_length_for_no_ocr=min_len, min_confidence=min_conf, budget=budget
    )