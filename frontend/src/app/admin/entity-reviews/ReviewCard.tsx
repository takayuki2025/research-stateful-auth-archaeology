"use client";

export default function ReviewCard({ review }: any) {
  const resolve = async (decision: string, value?: string) => {
    await fetch(`/api/entity-reviews/${review.id}/resolve`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        final_decision: decision,
        final_value: value,
      }),
    });
  };

  return (
    <div className="border rounded p-4">
      <h3 className="font-bold">{review.entity_type}</h3>

      <p>入力: {review.raw_value}</p>
      <p>
        提案: <strong>{review.proposed_value}</strong>（
        {Math.round(review.confidence * 100)}%）
      </p>

      <div className="flex gap-2 mt-3">
        <button
          onClick={() => resolve("accept", review.proposed_value)}
          className="bg-green-500 text-white px-3 py-1 rounded"
        >
          採用
        </button>

        <button
          onClick={() => resolve("reject")}
          className="bg-gray-300 px-3 py-1 rounded"
        >
          却下
        </button>

        <select
          onChange={(e) => resolve("override", e.target.value)}
          className="border px-2"
        >
          <option>候補選択</option>
          {review.candidates.map((c: any) => (
            <option key={c.value} value={c.value}>
              {c.value} ({Math.round(c.score * 100)}%)
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}
