from sqlalchemy import text
from sqlalchemy.orm import Session

class ReviewRepository:
    def __init__(self, db: Session):
        self.db = db

    def get_by_id(self, review_id: int):
        return self.db.execute(
            text("SELECT * FROM review_tasks WHERE id = :id"),
            {"id": review_id},
        ).mappings().first()

    def mark_resolved(self, review_id: int, action: str, resolved_value: str | None, note: str | None):
        # review_tasks
        self.db.execute(
            text("""
                UPDATE review_tasks
                SET status = 'resolved',
                    canonical_value = :value,
                    updated_at = NOW()
                WHERE id = :id
            """),
            {"id": review_id, "value": resolved_value, "id": review_id},
        )

        # review_events
        self.db.execute(
            text("""
                INSERT INTO review_events
                (review_task_id, action, resolved_value, note, created_at)
                VALUES (:rid, :action, :value, :note, NOW())
            """),
            {"rid": review_id, "action": action, "value": resolved_value, "note": note},
        )