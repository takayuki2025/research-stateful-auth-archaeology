from enum import Enum

class Decision(str, Enum):
    AUTO_ACCEPT = "auto_accept"
    NEEDS_REVIEW = "needs_review"
    REJECT = "rejected"