from __future__ import annotations

from dataclasses import dataclass
from typing import Any, Dict, List, Optional, Tuple

import yaml

from atlaskernel.domain.context import Context
from atlaskernel.services.audit_logger import audit_log
from atlaskernel.services.normalize import normalize_text


# ============================================================
# Decision Model
# ============================================================

@dataclass(frozen=True)
class PolicyDecision:
    decision: str  # "auto_accept" | "rejected" | "human_review"
    confidence: float
    rule_id: str
    detail: str


# ============================================================
# Policy Engine v2
# ============================================================

class PolicyEngineV2:
    """
    v2 の方針（確定版）:

    1. 同点トップは必ず human_review（最優先）
    2. alias は「候補確認後」にのみ auto_accept を許可
    3. similarity threshold は fallback
    4. context / multimodal は補正レイヤとして後付け
    """

    def __init__(
        self,
        policies_dir: str = "src/atlaskernel/policies/v2",
        brand_alias_path: str = "src/atlaskernel/assets/brand_alias.txt",
    ) -> None:
        self.policies_dir = policies_dir
        self.brand_alias = self._load_alias_map(brand_alias_path)

        self.base_policy = self._load_yaml(f"{policies_dir}/_base.yaml")
        self.entity_policies = {
            "brand": self._load_yaml(f"{policies_dir}/brand.yaml"),
            "condition": self._load_yaml(f"{policies_dir}/condition.yaml"),
            "color": self._load_yaml(f"{policies_dir}/color.yaml"),
        }

    # ============================================================
    # Public API
    # ============================================================

    def decide(
        self,
        entity_type: str,
        raw_value: str,
        candidates: List[Tuple[str, float]],
        ctx: Optional[Context] = None,
    ) -> PolicyDecision:
        ctx = ctx or Context()
        raw_norm = normalize_text(raw_value)

        # --------------------------------------------------------
        # 0. no candidates
        # --------------------------------------------------------
        if not candidates:
            decision = PolicyDecision(
                decision="rejected",
                confidence=0.0,
                rule_id="no_candidates",
                detail="no candidates",
            )
            audit_log("policy_decision", decision.__dict__)
            return decision

        # --------------------------------------------------------
        # 1. tie-break (最優先)
        # --------------------------------------------------------
        if len(candidates) >= 2 and candidates[0][1] == candidates[1][1]:
            decision = PolicyDecision(
                decision="human_review",
                confidence=candidates[0][1],
                rule_id="tie_break_human_review",
                detail="top scores tied",
            )
            audit_log("policy_decision", decision.__dict__)
            return decision

        # --------------------------------------------------------
        # 2. alias check（brand only / candidates 確認後）
        # --------------------------------------------------------
        if entity_type == "brand":
            aliased = self._alias_lookup(raw_norm)
            if aliased:
                for value, score in candidates:
                    if value == aliased:
                        decision = PolicyDecision(
                            decision="auto_accept",
                            confidence=max(0.95, score),
                            rule_id="brand_alias_auto_accept",
                            detail=f"alias:{raw_value}->{aliased}",
                        )
                        audit_log("policy_decision", decision.__dict__)
                        return decision

        # --------------------------------------------------------
        # 3. similarity thresholds
        # --------------------------------------------------------
        top_score = candidates[0][1]
        accept_th = float(self._get(entity_type, "threshold_auto_accept", 0.85))
        review_th = float(self._get(entity_type, "threshold_human_review", 0.35))

        top_score = self._apply_context_adjust(entity_type, top_score, ctx)

        if top_score >= accept_th:
            decision = PolicyDecision(
                decision="auto_accept",
                confidence=top_score,
                rule_id="threshold_auto_accept",
                detail=f"top={top_score}",
            )
            audit_log("policy_decision", decision.__dict__)
            return decision

        if top_score >= review_th:
            decision = PolicyDecision(
                decision="human_review",
                confidence=top_score,
                rule_id="threshold_human_review",
                detail=f"top={top_score}",
            )
            audit_log("policy_decision", decision.__dict__)
            return decision

        # --------------------------------------------------------
        # 4. reject
        # --------------------------------------------------------
        decision = PolicyDecision(
            decision="rejected",
            confidence=top_score,
            rule_id="threshold_reject",
            detail=f"top={top_score}",
        )
        audit_log("policy_decision", decision.__dict__)
        return decision

    # ============================================================
    # Internal helpers
    # ============================================================

    def _apply_context_adjust(self, entity_type: str, score: float, ctx: Context) -> float:
        """
        context / multimodal 拡張ポイント
        """
        if ctx.categories and entity_type == "brand":
            return min(1.0, score + 0.02)
        return score

    def _get(self, entity_type: str, key: str, default: Any) -> Any:
        base = self.base_policy.get(key, default)
        ent = self.entity_policies.get(entity_type, {}).get(key)
        return ent if ent is not None else base

    def _load_yaml(self, path: str) -> Dict[str, Any]:
        try:
            with open(path, "r", encoding="utf-8") as f:
                return yaml.safe_load(f) or {}
        except FileNotFoundError:
            return {}

    def _load_alias_map(self, path: str) -> Dict[str, str]:
        """
        brand_alias.txt:
          アップル,Apple
          あっぷる,Apple
        """
        m: Dict[str, str] = {}
        try:
            with open(path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if not line or line.startswith("#"):
                        continue
                    parts = [p.strip() for p in line.split(",")]
                    if len(parts) >= 2:
                        src = normalize_text(parts[0])
                        dst = parts[1].strip()
                        if src and dst:
                            m[src] = dst
        except FileNotFoundError:
            pass
        return m

    def _alias_lookup(self, raw_norm: str) -> Optional[str]:
        return self.brand_alias.get(raw_norm)