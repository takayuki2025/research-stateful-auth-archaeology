import json
from sqlalchemy import text
from sqlalchemy.engine import Engine

class ReviewRepository:
    def __init__(self, engine: Engine):
        self.engine = engine

    def fetch_human_reviews(self, limit: int = 50):
        sql = text("""
            SELECT * FROM review_tasks
            WHERE decision = 'human_review'
              AND status = 'pending'
            ORDER BY created_at ASC
            LIMIT :limit
        """)
        with self.engine.begin() as conn:
            rows = conn.execute(sql, {"limit": limit}).mappings().all()
            return rows

    def resolve(self, task_id: int, action: str, canonical_value: str | None):
        with self.engine.begin() as conn:
            if action in ("accept", "override"):
                conn.execute(
                    text("""
                        UPDATE review_tasks
                        SET status='resolved',
                            decision='auto_accept',
                            canonical_value=:cv
                        WHERE id=:id
                    """),
                    {"id": task_id, "cv": canonical_value},
                )
            else:
                conn.execute(
                    text("""
                        UPDATE review_tasks
                        SET status='resolved',
                            decision='rejected'
                        WHERE id=:id
                    """),
                    {"id": task_id},
                )
