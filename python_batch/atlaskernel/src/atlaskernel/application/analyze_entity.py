from __future__ import annotations

import os
from typing import List, Dict, Any

from atlaskernel.services.token_splitter import split_entities
from atlaskernel.application.pipelines.brand_pipeline import analyze_brand
from atlaskernel.application.pipelines.condition_pipeline import analyze_condition
from atlaskernel.application.pipelines.color_pipeline import analyze_color

from atlaskernel.services.policy_engine import PolicyEngine
from atlaskernel.services.policy_engine_v2 import PolicyEngineV2

from atlaskernel.services.context_builder import ContextBuilder
from atlaskernel.domain.request import AnalysisRequest
from atlaskernel.domain.context import Context


def analyze(request: AnalysisRequest) -> List[Dict[str, Any]]:
    # 0) policy
    engine_name = os.getenv("ATLAS_POLICY_ENGINE", "v1")
    policy = PolicyEngineV2() if engine_name == "v2" else PolicyEngine()

    # 1) context
    ctx: Context = ContextBuilder().build(base={}, multimodal=None)

    # 2) raw_text 側（brandは作らない）
    parts = split_entities(request.raw_value, mode="raw")
    print("[DEBUG parts]", parts)

    # 3) brand_text 優先（attributesモード）
    req_ctx = getattr(request, "context", {}) or {}
    brand_text = req_ctx.get("brand_text") or req_ctx.get("attributes_text")
    print("[DEBUG brand_text]", brand_text)

    hint_parts = (
        split_entities(brand_text, mode="attributes")
        if brand_text
        else {"brand": "", "condition": "", "color": "", "rest": ""}
    )
    print("[DEBUG hint_parts]", hint_parts)

    results: List[Dict[str, Any]] = []

    # ----------------------------------------------------------
    # brand
    # ----------------------------------------------------------
    brand_input = (hint_parts.get("brand") or hint_parts.get("rest")) if brand_text else parts.get("brand")
    print("[DEBUG brand_input]", brand_input)

    if brand_input and str(brand_input).strip():
        results.append(
            analyze_brand(
                AnalysisRequest(
                    entity_type="brand",
                    raw_value=str(brand_input),
                    known_assets_ref="brands_v1",
                    context=req_ctx,
                ),
                policy,
                ctx,
            )
        )

    # ----------------------------------------------------------
    # condition
    # ----------------------------------------------------------
    condition_input = hint_parts.get("condition") if brand_text else parts.get("condition")
    if condition_input and str(condition_input).strip():
        results.append(
            analyze_condition(
                AnalysisRequest(
                    entity_type="condition",
                    raw_value=str(condition_input),
                    known_assets_ref="conditions_v1",
                    context=req_ctx,
                ),
                policy,
                ctx,
            )
        )

    # ----------------------------------------------------------
    # color
    # ----------------------------------------------------------
    color_input = hint_parts.get("color") if brand_text else parts.get("color")
    if color_input and str(color_input).strip():
        results.append(
            analyze_color(
                AnalysisRequest(
                    entity_type="color",
                    raw_value=str(color_input),
                    known_assets_ref="colors_v1",
                    context=req_ctx,
                ),
                policy,
                ctx,
            )
        )

    return results