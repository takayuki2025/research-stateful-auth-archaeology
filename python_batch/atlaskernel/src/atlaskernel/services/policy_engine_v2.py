from __future__ import annotations

from dataclasses import dataclass
from typing import Any, Dict, List, Optional, Tuple

import yaml

from atlaskernel.domain.context import Context
from atlaskernel.services.audit_logger import audit_log  # 既存を利用する想定
from atlaskernel.services.similarities import best_candidates  # 既存の類似度計算を利用する想定
from atlaskernel.services.normalize import normalize_text      # 既存正規化を利用する想定


@dataclass(frozen=True)
class PolicyDecision:
    decision: str  # "auto_accept" | "rejected" | "human_review"
    confidence: float
    rule_id: str
    detail: str


class PolicyEngineV2:
    """
    v2 の方針:
    1) alias（辞書）で確定できるものは強く auto_accept
    2) similarity は従来通り（ただし低い場合は human_review へ）
    3) context/multimodal は “補正レイヤ” として後付け可能
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

    def decide(
        self,
        entity_type: str,
        raw_value: str,
        candidates: List[Tuple[str, float]],
        ctx: Optional[Context] = None,
    ) -> PolicyDecision:
        ctx = ctx or Context()

        raw_norm = normalize_text(raw_value)

        # 1) alias 優先（brand のみ）
        if entity_type == "brand":
            aliased = self._alias_lookup(raw_norm)
            if aliased:
                return PolicyDecision(
                    decision="auto_accept",
                    confidence=0.95,
                    rule_id="brand_alias_auto_accept",
                    detail=f"alias:{raw_value}->{aliased}",
                )

        # 2) similarity threshold
        top_score = candidates[0][1] if candidates else 0.0
        accept_th = float(self._get(entity_type, "threshold_auto_accept", 0.85))
        review_th = float(self._get(entity_type, "threshold_human_review", 0.35))

        # context 補正（今は最小：カテゴリで brand を落としにくくする等、後で拡張）
        top_score = self._apply_context_adjust(entity_type, top_score, ctx)

        if top_score >= accept_th:
            return PolicyDecision(
                decision="auto_accept",
                confidence=top_score,
                rule_id="threshold_auto_accept",
                detail=f"top={top_score}",
            )

        if top_score >= review_th:
            return PolicyDecision(
                decision="human_review",
                confidence=top_score,
                rule_id="threshold_human_review",
                detail=f"top={top_score}",
            )

        return PolicyDecision(
            decision="rejected",
            confidence=top_score,
            rule_id="threshold_reject",
            detail=f"top={top_score}",
        )

    def _apply_context_adjust(self, entity_type: str, score: float, ctx: Context) -> float:
        """
        ここが “context/multimodal 対応” の中心拡張点。
        今は最小：カテゴリが存在するなら僅かに補正、程度。
        """
        if ctx.categories and entity_type == "brand":
            # 例：カテゴリ情報がある＝手がかりが多いので若干上げる（将来は学習で置換）
            return min(1.0, score + 0.02)
        return score

    def _get(self, entity_type: str, key: str, default: Any) -> Any:
        base = self.base_policy.get(key, default)
        ent = self.entity_policies.get(entity_type, {}).get(key)
        return ent if ent is not None else base

    def _load_yaml(self, path: str) -> Dict[str, Any]:
        with open(path, "r", encoding="utf-8") as f:
            return yaml.safe_load(f) or {}

    def _load_alias_map(self, path: str) -> Dict[str, str]:
        """
        brand_alias.txt のフォーマット（例）:
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