"""create review tables

Revision ID: 6cc385ed422c
Revises:
Create Date: 2026-01-10 04:06:24.037743

"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa
from sqlalchemy.dialects import mysql

# revision identifiers, used by Alembic.
revision: str = '6cc385ed422c'
down_revision: Union[str, Sequence[str], None] = None
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade():
    op.create_table(
        "review_tasks",
        sa.Column("id", sa.BigInteger, primary_key=True),
        sa.Column("entity_type", sa.String(32), nullable=False),
        sa.Column("raw_value", sa.String(255), nullable=False),

        # AI judgment
        sa.Column("decision", sa.String(32), nullable=False),
        sa.Column("confidence", sa.Float, nullable=False),
        sa.Column("rule_id", sa.String(128), nullable=False),

        # canonical
        sa.Column("canonical_value", sa.String(255)),

        # explainability
        sa.Column("candidates_json", sa.JSON, nullable=False),
        sa.Column("policy_trace_json", sa.JSON),
        sa.Column("knowledge_sources_json", sa.JSON),

        # workflow
        sa.Column("status", sa.String(32), nullable=False, server_default="pending"),

        sa.Column("created_at", sa.DateTime, server_default=sa.text("CURRENT_TIMESTAMP")),
        sa.Column(
            "updated_at",
            sa.DateTime,
            server_default=sa.text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"),
        ),
    )

    op.create_table(
        "review_events",
        sa.Column("id", sa.BigInteger, primary_key=True),
        sa.Column("review_task_id", sa.BigInteger, nullable=False),

        sa.Column("action", sa.String(32), nullable=False),
        sa.Column("note", sa.Text),
        sa.Column("resolved_value", sa.String(255)),

        sa.Column("created_at", sa.DateTime, server_default=sa.text("CURRENT_TIMESTAMP")),

        sa.ForeignKeyConstraint(
            ["review_task_id"],
            ["review_tasks.id"],
            ondelete="CASCADE",
        ),
    )


def downgrade():
    op.drop_table("review_events")
    op.drop_table("review_tasks")