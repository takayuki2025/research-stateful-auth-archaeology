from abc import ABC, abstractmethod
from typing import Optional

from ..policy_context import PolicyContext
from ..policy_input import PolicyInput
from ..policy_result import PolicyResult


class PolicyRule(ABC):
    @abstractmethod
    def evaluate(self, policy_input: PolicyInput, context: PolicyContext) -> Optional[PolicyResult]:
        raise NotImplementedError