"""add knowledge entities and verifications

Revision ID: 408801ce33d5
Revises: 6cc385ed422c
Create Date: 2026-01-10 04:29:44.509646

"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision: str = '408801ce33d5'
down_revision: Union[str, Sequence[str], None] = '6cc385ed422c'
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    op.create_table(
    "knowledge_entities",
    sa.Column("id", sa.BigInteger, primary_key=True),
    sa.Column("entity_type", sa.String(32), nullable=False),
    sa.Column("canonical_value", sa.String(255), nullable=False),

    sa.Column(
        "status",
        sa.String(32),
        nullable=False,
        server_default="active",
    ),

    sa.Column("created_at", sa.DateTime, server_default=sa.text("CURRENT_TIMESTAMP")),
    sa.Column(
        "updated_at",
        sa.DateTime,
        server_default=sa.text("CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"),
    ),

    sa.UniqueConstraint("entity_type", "canonical_value", name="uq_entity_canonical"),
)
    op.create_table(
    "knowledge_verifications",
    sa.Column("id", sa.BigInteger, primary_key=True),

    sa.Column(
        "knowledge_entity_id",
        sa.BigInteger,
        sa.ForeignKey("knowledge_entities.id", ondelete="CASCADE"),
        nullable=False,
    ),

    sa.Column("verification_type", sa.String(16), nullable=False),  # human / ai / hybrid
    sa.Column("verified_by", sa.String(128), nullable=False),      # user_id or ai name
    sa.Column("verification_model", sa.String(64), nullable=True),
    sa.Column("verification_date", sa.DateTime, nullable=False),

    sa.Column("sources_json", sa.JSON, nullable=False),
    sa.Column("confidence", sa.Float, nullable=False),
    sa.Column("note", sa.Text),

    sa.Column("created_at", sa.DateTime, server_default=sa.text("CURRENT_TIMESTAMP")),
)
    # pass


def downgrade() -> None:
    # 作成時と逆の順番で消す（外部キーの関係上、verificationsを先に消す）
    op.drop_table("knowledge_verifications")
    op.drop_table("knowledge_entities")
