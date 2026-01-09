from .base_rule import PolicyRule
from ..decision import Decision
from ..policy_result import PolicyResult, PolicyAction

class SimilarityLowRule(PolicyRule):

    def __init__(self, low_threshold: float):
        self.low_threshold = low_threshold

    def evaluate(self, policy_input, context):
        if policy_input.top_score < self.low_threshold:
            return PolicyResult(
                decision=Decision.NEEDS_REVIEW,
                canonical_value=None,
                confidence=policy_input.top_score,
                rule_id="similarity_low",
                trace={
                    "low_threshold": self.low_threshold,
                    "top_score": policy_input.top_score,
                },
                action=PolicyAction.REVIEW,
            )
        return None