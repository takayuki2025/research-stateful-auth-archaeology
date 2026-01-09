from .base_rule import PolicyRule
from ..action import PolicyAction
from ..decision import Decision
from ..policy_result import PolicyResult


class ThresholdRule(PolicyRule):
    def __init__(self, threshold: float):
        self.threshold = threshold

    def evaluate(self, policy_input, context):
        if not policy_input.candidates:
            return None

        if policy_input.top_score >= self.threshold:
            return PolicyResult(
                decision=Decision.AUTO_ACCEPT,
                action=PolicyAction.COMMIT,
                canonical_value=policy_input.candidates[0].value,
                confidence=policy_input.top_score,
                rule_id="threshold_auto_accept",
                trace={"threshold": self.threshold, "top_score": policy_input.top_score},
            )
        return None