"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { AdminNav } from "@/components/layout/AdminNav";

type ItemEntity = {
  id: number;
  item_id: number;
  entity_type: string;
  raw_value: string;
  canonical_value: string;
  confidence: number;
  decision: "auto_accept" | "needs_review" | "rejected";
};

const API_BASE =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://laravel.test:4430";

const REASONS = [
  { value: "manual_reanalyze", label: "手動再解析" },
  { value: "policy_updated", label: "Policy 更新後" },
  { value: "assets_updated", label: "辞書更新後" },
  { value: "human_feedback", label: "人間レビュー反映" },
] as const;

function DecisionBadge({ decision }: { decision: string }) {
  const map: Record<string, string> = {
    auto_accept: "#16a34a",
    needs_review: "#eab308",
    rejected: "#dc2626",
  };

  return (
    <span
      style={{
        padding: "4px 8px",
        borderRadius: 6,
        fontSize: 12,
        color: "#fff",
        backgroundColor: map[decision] ?? "#6b7280",
      }}
    >
      {decision}
    </span>
  );
}

function ConfidenceBar({ value }: { value: number }) {
  const percent = Math.round(value * 100);
  const color =
    percent >= 90 ? "#16a34a" : percent >= 70 ? "#2563eb" : "#ea580c";

  return (
    <div style={{ width: 100 }}>
      <div style={{ fontSize: 12 }}>{percent}%</div>
      <div style={{ height: 6, background: "#eee", borderRadius: 4 }}>
        <div
          style={{
            width: `${percent}%`,
            height: "100%",
            background: color,
            borderRadius: 4,
          }}
        />
      </div>
    </div>
  );
}

export default function EntityReviewsPage() {
  const [rows, setRows] = useState<ItemEntity[]>([]);
  const [loading, setLoading] = useState(true);
  const [reason, setReason] = useState("manual_reanalyze");

  async function load() {
    setLoading(true);
    const res = await fetch(`${API_BASE}/api/entity-reviews`, {
      credentials: "include",
    });
    const json = await res.json();
    setRows(json.data ?? []);
    setLoading(false);
  }

  async function act(id: number, action: "approve" | "reject") {
    await fetch(`${API_BASE}/api/entity-reviews/${id}/${action}`, {
      method: "POST",
      credentials: "include",
    });
    await load();
  }

  async function reanalyze(id: number) {
    await fetch(`${API_BASE}/api/entity-reviews/${id}/reanalyze`, {
      method: "POST",
      credentials: "include",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ reason }),
    });
    await load();
  }

  useEffect(() => {
    load();
  }, []);

  return (
    <div style={{ padding: 24 }}>
      <AdminNav />

      <h1>Entity Reviews</h1>

      <div style={{ marginBottom: 12 }}>
        再解析理由：
        <select
          value={reason}
          onChange={(e) => setReason(e.target.value)}
          style={{ marginLeft: 8 }}
        >
          {REASONS.map((r) => (
            <option key={r.value} value={r.value}>
              {r.label}
            </option>
          ))}
        </select>
      </div>

      {loading ? (
        <p>Loading...</p>
      ) : rows.length === 0 ? (
        <p>🎉 No items need review.</p>
      ) : (
        <table width="100%" cellPadding={10}>
          <thead>
            <tr>
              <th>ID</th>
              <th>Item</th>
              <th>Type</th>
              <th>Raw</th>
              <th>Canonical</th>
              <th>Confidence</th>
              <th>Decision</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td>{r.id}</td>
                <td>{r.item_id}</td>
                <td>{r.entity_type}</td>
                <td>{r.raw_value}</td>
                <td>{r.canonical_value}</td>
                <td>
                  <ConfidenceBar value={r.confidence} />
                </td>
                <td>
                  <DecisionBadge decision={r.decision} />
                </td>
                <td>
                  <button onClick={() => act(r.id, "approve")}>Approve</button>{" "}
                  <button onClick={() => act(r.id, "reject")}>Reject</button>{" "}
                  <button onClick={() => reanalyze(r.id)}>Reanalyze</button>{" "}
                  <Link href={`/admin/entity-audits/${r.id}`}>Audits</Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
