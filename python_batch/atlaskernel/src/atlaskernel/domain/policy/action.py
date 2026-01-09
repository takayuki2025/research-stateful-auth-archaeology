from enum import Enum


class PolicyAction(str, Enum):
    COMMIT = "commit"          # 自動確定
    SEND_TO_REVIEW = "review"  # 人手へ
    DROP = "drop"              # 破棄（不正/無意味）