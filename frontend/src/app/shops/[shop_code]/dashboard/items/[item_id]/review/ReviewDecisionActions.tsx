"use client";

import { useState } from "react";

/**
 * ✅ 最小の型固定（noImplicitAny 対策）
 * - analysis の必要部分だけを型に落とす（他は unknown で逃がす）
 * - 既存機能/デザインは一切変更しない
 */
type Props = {
  itemId: number | string;
  analysis: {
    normalization: {
      brand_entity_id: number | null;
    };
    tags: unknown; // 配列/オブジェクトなど何でも来うるので最小で unknown
  };
};

export function ReviewDecisionActions({ itemId, analysis }: Props) {
  const [submitting, setSubmitting] = useState(false);

  const handleApply = async () => {
    setSubmitting(true);

    await fetch(`/api/items/${itemId}/analysis/apply`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        decision: {
          brand_entity_id: analysis.normalization.brand_entity_id,
          tags: analysis.tags,
        },
      }),
    });

    setSubmitting(false);
    alert("確定しました");
  };

  return (
    <div style={{ marginTop: 24 }}>
      <button disabled={submitting} onClick={handleApply}>
        AI解析結果を確定する
      </button>
    </div>
  );
}
