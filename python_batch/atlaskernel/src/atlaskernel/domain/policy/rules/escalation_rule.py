from .base_rule import PolicyRule
from ..decisions import Decision
from ..policy_result import PolicyResult
from ..action import PolicyAction

class ContextEscalationRule(PolicyRule):
    def evaluate(self, policy_input, context):
        if context.image_features and context.image_features.get("brand_conflict"):
            return PolicyResult(
                decision=Decision.NEEDS_REVIEW,
                action=PolicyAction.REVIEW,
                canonical_value=None,
                confidence=policy_input.top_score,
                rule_id="image_conflict",
                trace=context.image_features,
            )
        return None