from atlaskernel.services.token_splitter import split_entities
from atlaskernel.application.pipelines.brand_pipeline import analyze_brand
from atlaskernel.application.pipelines.condition_pipeline import analyze_condition
from atlaskernel.application.pipelines.color_pipeline import analyze_color
from atlaskernel.services.policy_engine import PolicyEngine
from atlaskernel.domain.request import AnalysisRequest


def analyze(request: AnalysisRequest):
    policy = PolicyEngine()
    parts = split_entities(request.raw_value)

    print("[DEBUG parts]", parts)

    results = []

    # ===== brand =====
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
            )
        )

    # ===== condition =====
    if parts["condition"]:
        results.append(
            analyze_condition(
                AnalysisRequest(
                    entity_type="condition",
                    raw_value=parts["condition"],
                    known_assets_ref="conditions_v1",
                ),
                policy,
            )
        )

    # ===== color =====
    if parts["color"]:
        results.append(
            analyze_color(
                AnalysisRequest(
                    entity_type="color",
                    raw_value=parts["color"],
                    known_assets_ref="colors_v1",
                ),
                policy,
            )
        )

    return results