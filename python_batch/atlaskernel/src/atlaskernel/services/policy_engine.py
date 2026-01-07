import yaml
from importlib import resources


class PolicyEngine:
    def load(self, entity_type: str) -> dict:
        resource = f"{entity_type}.yaml"
        try:
            with resources.files("atlaskernel.policies").joinpath(resource).open(
                "r", encoding="utf-8"
            ) as f:
                return yaml.safe_load(f)
        except FileNotFoundError:
            raise FileNotFoundError(
                f"Policy not found: atlaskernel.policies/{resource}"
            )

    def evaluate(self, policy: dict, context: dict):
        score = context["score"]

        for rule in policy.get("rules", []):
            when = rule.get("when", {})
            if self._match(when, context):
                return (
                    rule["decision"],
                    rule.get("reason"),
                    {
                        "policy_schema": policy.get("schema"),
                        "entity_type": policy.get("entity_type"),
                        "rule_id": rule.get("id"),
                        "score": score,
                    },
                )

        actions = (
            policy.get("overrides", {}).get("actions")
            or policy.get("defaults", {}).get("actions", {})
        )

        if score >= actions["auto_accept"]["min_score"]:
            return "auto_accept", "threshold", {
                "rule_id": "threshold_auto_accept",
                "score": score,
            }
        if score >= actions["needs_review"]["min_score"]:
            return "needs_review", "threshold", {
                "rule_id": "threshold_needs_review",
                "score": score,
            }
        return "rejected", "threshold", {
            "rule_id": "threshold_rejected",
            "score": score,
        }

    def _match(self, when: dict, context: dict) -> bool:
        score = context["score"]
        if "score_gte" in when and score < when["score_gte"]:
            return False
        if "score_lt" in when and score >= when["score_lt"]:
            return False
        if "has_alias" in when and context.get("has_alias") != when["has_alias"]:
            return False
        return True