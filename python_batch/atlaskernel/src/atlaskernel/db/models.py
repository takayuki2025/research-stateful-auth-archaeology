from sqlalchemy import (
    Column, BigInteger, String, Float, JSON, Text, DateTime, ForeignKey
)
from sqlalchemy.sql import func
from .base import Base

class ReviewTask(Base):
    __tablename__ = "review_tasks"

    id = Column(BigInteger, primary_key=True)
    entity_type = Column(String(32), nullable=False)
    raw_value = Column(String(255), nullable=False)

    decision = Column(String(32), nullable=False)
    confidence = Column(Float, nullable=False)
    rule_id = Column(String(128), nullable=False)

    canonical_value = Column(String(255))
    candidates_json = Column(JSON, nullable=False)
    policy_trace_json = Column(JSON)
    knowledge_sources_json = Column(JSON)

    status = Column(String(32), nullable=False, default="pending")

    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())


class ReviewEvent(Base):
    __tablename__ = "review_events"

    id = Column(BigInteger, primary_key=True)
    review_task_id = Column(BigInteger, ForeignKey("review_tasks.id"), nullable=False)

    action = Column(String(32), nullable=False)
    note = Column(Text)
    resolved_value = Column(String(255))

    created_at = Column(DateTime, server_default=func.now())