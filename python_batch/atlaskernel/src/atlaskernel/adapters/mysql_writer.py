import os
import mysql.connector


def write_results_to_db(pairs):
    conn = mysql.connector.connect(
        host=os.getenv("DB_HOST"),
        port=int(os.getenv("DB_PORT", 3306)),
        user=os.getenv("DB_USER"),
        password=os.getenv("DB_PASS"),
        database=os.getenv("DB_NAME"),
        autocommit=False,
    )

    cursor = conn.cursor(dictionary=True)

    try:
        for item_id, result in pairs:
            # =====================================================
            # ① デバッグ：受け取った結果をすべて表示
            # =====================================================
            print(
                "[DEBUG pair]",
                "item_id=", item_id,
                "entity_type=", result.entity_type,
                "raw=", result.raw_value,
                "canonical=", result.canonical_value,
                "confidence=", result.confidence,
                "decision=", result.decision,
            )

            # =====================================================
            # ② item_entities のベース行を必ず作る（v1思想）
            # =====================================================
            cursor.execute(
                """
                INSERT INTO item_entities
                  (item_id, generated_version, generated_at, created_at, updated_at)
                VALUES
                  (%s, 'v1', NOW(), NOW(), NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()
                """,
                (item_id,),
            )

            # canonical が無ければ entity 保存しない（ログだけ出す）
            if not result.canonical_value:
                print("[DEBUG skip] no canonical_value")
                continue

            normalized_key = result.canonical_value.lower()

            # =====================================================
            # ③ entity 種別判定
            # =====================================================
            if result.entity_type == "brand":
                table, column = "brand_entities", "brand_entity_id"
            elif result.entity_type == "condition":
                table, column = "condition_entities", "condition_entity_id"
            elif result.entity_type == "color":
                table, column = "color_entities", "color_entity_id"
            else:
                print("[DEBUG skip] unknown entity_type:", result.entity_type)
                continue

            # =====================================================
            # ④ entity の upsert
            # =====================================================
            cursor.execute(
                f"SELECT id FROM {table} WHERE normalized_key=%s LIMIT 1",
                (normalized_key,),
            )
            row = cursor.fetchone()

            if row:
                entity_id = row["id"]
                print(f"[DEBUG reuse] {table} id={entity_id}")
            else:
                cursor.execute(
                    f"""
                    INSERT INTO {table}
                      (canonical_name, normalized_key, confidence, created_from, created_at, updated_at)
                    VALUES
                      (%s, %s, %s, %s, NOW(), NOW())
                    """,
                    (
                        result.canonical_value,
                        normalized_key,
                        result.confidence,
                        "atlaskernel_v1",
                    ),
                )
                entity_id = cursor.lastrowid
                print(f"[DEBUG insert] {table} id={entity_id}")

            # =====================================================
            # ⑤ item_entities に関連付け
            # =====================================================
            cursor.execute(
                f"""
                UPDATE item_entities
                SET {column} = %s,
                    generated_at = NOW(),
                    updated_at = NOW()
                WHERE item_id = %s
                """,
                (entity_id, item_id),
            )

            print(
                f"[DEBUG link] item_id={item_id} "
                f"{column}={entity_id}"
            )

        conn.commit()
        print("[OK] AtlasKernel DB pipeline committed")

    except Exception as e:
        conn.rollback()
        print("[ERROR] rollback:", e)
        raise

    finally:
        cursor.close()
        conn.close()