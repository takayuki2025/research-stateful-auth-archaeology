from __future__ import annotations

import os
from typing import List, Dict, Any

from atlaskernel.services.token_splitter import split_entities
from atlaskernel.application.pipelines.brand_pipeline import analyze_brand
from atlaskernel.application.pipelines.condition_pipeline import analyze_condition
from atlaskernel.application.pipelines.color_pipeline import analyze_color

from atlaskernel.services.policy_engine import PolicyEngine          # v1
from atlaskernel.services.policy_engine_v2 import PolicyEngineV2    # v2

from atlaskernel.services.context_builder import ContextBuilder
from atlaskernel.domain.request import AnalysisRequest
from atlaskernel.domain.context import Context
from atlaskernel.domain.multimodal import MultimodalRef


def analyze(request: AnalysisRequest) -> List[Dict[str, Any]]:
    """
    AtlasKernel v0.2.0
    - PolicyEngine v1 / v2 切替対応
    - Context / Multimodal 拡張前提
    - brand / condition / color 自動解析（人手レビュー余地あり）
    """

    # ==========================================================
    # 0. PolicyEngine 選択（env で切替）
    # ==========================================================
    engine_name = os.getenv("ATLAS_POLICY_ENGINE", "v1")

    if engine_name == "v2":
        policy = PolicyEngineV2()
    else:
        policy = PolicyEngine()

    # ==========================================================
    # 1. Context 構築（今は空でもOK）
    # ==========================================================
    context_builder = ContextBuilder()

    # 将来：request.context / request.multimodal をここに足す
    ctx: Context = context_builder.build(
        base={},                    # locale / category / shop_id 等
        multimodal=None             # MultimodalRef（画像・音）
    )

    # ==========================================================
    # 2. トークン分解
    # ==========================================================
    parts = split_entities(request.raw_value)
    print("[DEBUG parts]", parts)

    results: List[Dict[str, Any]] = []

    # ==========================================================
    # 3. brand
    # ==========================================================
    brand_input = parts["brand"] or parts["rest"]
    if brand_input:
        results.append(
            analyze_brand(
                AnalysisRequest(
                    entity_type="brand",
                    raw_value=brand_input,
                    known_assets_ref="brands_v1",
                ),
                policy,
                ctx,            # ← ★ v2 用拡張（v1 は無視）
            )
        )

    # ==========================================================
    # 4. condition
    # ==========================================================
    if parts["condition"]:
        results.append(
            analyze_condition(
                AnalysisRequest(
                    entity_type="condition",
                    raw_value=parts["condition"],
                    known_assets_ref="conditions_v1",
                ),
                policy,
                ctx,
            )
        )

    # ==========================================================
    # 5. color
    # ==========================================================
    if parts["color"]:
        results.append(
            analyze_color(
                AnalysisRequest(
                    entity_type="color",
                    raw_value=parts["color"],
                    known_assets_ref="colors_v1",
                ),
                policy,
                ctx,
            )
        )

    return results