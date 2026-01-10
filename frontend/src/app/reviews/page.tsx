"use client";
import { useEffect, useState } from "react";
import { fetchReviews, resolveReview } from "@/lib/reviews";

export default function ReviewPage() {
  const [items, setItems] = useState<any[]>([]);

  useEffect(() => {
    fetchReviews().then(setItems);
  }, []);

  return (
    <div>
      <h1>Review Queue</h1>
      {items.map((r) => (
        <div
          key={r.id}
          style={{ border: "1px solid #ccc", margin: 8, padding: 8 }}
        >
          <b>{r.entity_type}</b> : {r.raw_value}
          <pre>{JSON.stringify(r.candidates_json, null, 2)}</pre>
          <button onClick={() => resolveReview(r.id, "accept")}>Accept</button>
          <button onClick={() => resolveReview(r.id, "reject")}>Reject</button>
        </div>
      ))}
    </div>
  );
}
