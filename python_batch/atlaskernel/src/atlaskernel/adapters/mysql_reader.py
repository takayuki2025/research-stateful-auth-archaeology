import os
import mysql.connector
from atlaskernel.domain.request import AnalysisRequest


def read_requests_from_db(limit=20, offset=0):
    conn = mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        port=int(os.getenv("DB_PORT", 3306)),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASS"),
        database=os.getenv("DB_NAME"),
    )

    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT id, brand
        FROM items
        ORDER BY id ASC
        LIMIT %s OFFSET %s
        """,
        (limit, offset),
    )

    rows = cursor.fetchall()

    for row in rows:
        req = AnalysisRequest(
            entity_type="brand",
            raw_value=row.get("brand"),
            context=None,
        )
        # ★ item_id とセットで返す
        yield row["id"], req

    cursor.close()
    conn.close()