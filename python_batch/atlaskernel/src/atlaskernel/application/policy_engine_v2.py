from atlaskernel.domain.policy.rules.threshold_rule import ThresholdRule
from atlaskernel.domain.policy.rules.similarity_rule import SimilarityLowRule
from atlaskernel.domain.policy.rules.escalation_rule import ContextEscalationRule
from atlaskernel.domain.policy.policy_result import PolicyResult
from atlaskernel.domain.policy.decision import Decision
from atlaskernel.domain.policy.action import PolicyAction

class PolicyEngineV2:
    def __init__(self):
        self.rules = [
            # ★画像矛盾は最優先
            ContextEscalationRule(),
            ThresholdRule(threshold=0.85),
            SimilarityLowRule(low_threshold=0.4),
        ]

    def decide(self, policy_input, context) -> PolicyResult:
        for rule in self.rules:
            result = rule.evaluate(policy_input, context)
            if result is not None:
                return result

        return PolicyResult(
            decision=Decision.NEEDS_REVIEW,
            action=PolicyAction.REVIEW,
            canonical_value=None,
            confidence=policy_input.top_score,
            rule_id="fallback",
            trace={},
        )