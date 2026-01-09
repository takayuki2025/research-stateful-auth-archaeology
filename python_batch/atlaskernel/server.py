from .base_rule import PolicyRule
from ..decisions import Decision
from ..policy_result import PolicyResult
from ..action import PolicyAction

class SimilarityLowRule(PolicyRule):
    def __init__(self, low_threshold: float = 0.4):
        self.low_threshold = low_threshold

    def evaluate(self, policy_input, context):
        if policy_input.top_score < self.low_threshold:
            return PolicyResult(
                decision=Decision.NEEDS_REVIEW,
                action=PolicyAction.REVIEW,
                canonical_value=None,
                confidence=policy_input.top_score,
                rule_id="similarity_low",
                trace={"low_threshold": self.low_threshold, "top_score": policy_input.top_score},
            )
        return None