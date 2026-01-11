"use client";

import { useState } from "react";

export function ReviewDecisionActions({ itemId, analysis }) {
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
