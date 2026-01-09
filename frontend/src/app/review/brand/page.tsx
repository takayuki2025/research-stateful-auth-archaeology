"use client";

import { useEffect, useState } from "react";
import Link from "next/link";

type ReviewRow = {
  id: string;
  entity_type: "brand";
  raw_value: string;
  top_candidate?: { value: string; score: number } | null;
  policy_rule_id: string;
  created_at: string;
};

export default function BrandReviewQueuePage() {
  const [rows, setRows] = useState<ReviewRow[]>([]);
  const [status, setStatus] = useState<"pending" | "done">("pending");
  const [q, setQ] = useState("");

  useEffect(() => {
    const run = async () => {
      const res = await fetch(
        `/api/review/brand?status=${status}&q=${encodeURIComponent(q)}`,
        {
          cache: "no-store",
        }
      );
      const data = await res.json();
      setRows(data.items ?? []);
    };
    run();
  }, [status, q]);

  return (
    <div style={{ padding: 24 }}>
      <h1 style={{ fontSize: 22, fontWeight: 700 }}>Review Queue: Brand</h1>

      <div
        style={{
          display: "flex",
          gap: 12,
          marginTop: 12,
          alignItems: "center",
        }}
      >
        <select
          value={status}
          onChange={(e) => setStatus(e.target.value as any)}
        >
          <option value="pending">Pending</option>
          <option value="done">Done</option>
        </select>

        <input
          placeholder="search raw_value..."
          value={q}
          onChange={(e) => setQ(e.target.value)}
          style={{ padding: 8, width: 320 }}
        />
      </div>

      <table
        style={{ width: "100%", marginTop: 16, borderCollapse: "collapse" }}
      >
        <thead>
          <tr>
            <th style={{ textAlign: "left" }}>Raw</th>
            <th style={{ textAlign: "left" }}>Top</th>
            <th style={{ textAlign: "left" }}>Rule</th>
            <th style={{ textAlign: "left" }}>Created</th>
            <th />
          </tr>
        </thead>
        <tbody>
          {rows.map((r) => (
            <tr key={r.id} style={{ borderTop: "1px solid #ddd" }}>
              <td style={{ padding: 10 }}>{r.raw_value}</td>
              <td style={{ padding: 10 }}>
                {r.top_candidate
                  ? `${r.top_candidate.value} (${r.top_candidate.score.toFixed(2)})`
                  : "-"}
              </td>
              <td style={{ padding: 10 }}>{r.policy_rule_id}</td>
              <td style={{ padding: 10 }}>
                {new Date(r.created_at).toLocaleString()}
              </td>
              <td style={{ padding: 10 }}>
                <Link href={`/review/brand/${r.id}`}>Open</Link>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
