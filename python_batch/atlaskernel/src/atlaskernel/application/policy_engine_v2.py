from atlaskernel.domain.policy.decision import Decision
from atlaskernel.domain.policy.policy_result import PolicyResult
from atlaskernel.domain.policy.rules.escalation_rule import ContextEscalationRule
from atlaskernel.domain.policy.rules.similarity_rule import SimilarityLowRule
from atlaskernel.domain.policy.rules.threshold_rule import ThresholdRule


class PolicyEngineV2:
    def __init__(self):
        # 最小の強い順序：
        # 1) 高スコアは自動確定
        # 2) 文脈衝突（画像等）があれば人手へ
        # 3) 低スコアは人手へ
        self.rules = [
            ThresholdRule(threshold=0.85),
            ContextEscalationRule(),
            SimilarityLowRule(low_threshold=0.4),
        ]

    def decide(self, policy_input, context) -> PolicyResult:
        for rule in self.rules:
            result = rule.evaluate(policy_input, context)
            if result is not None:
                return result

        # fallback（中間域は人手へ）
        return PolicyResult(
            decision=Decision.NEEDS_REVIEW,
            action=result.action if (result := None) else None,  # 使わないが型崩れ防止
            canonical_value=None,
            confidence=policy_input.top_score,
            rule_id="fallback",
            trace={"top_score": policy_input.top_score},
        )
