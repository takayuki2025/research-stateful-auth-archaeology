"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { AdminNav } from "@/components/layout/AdminNav";

type Audit = {
  id: number;
  decision: string;
  confidence: number;
  payload: any;
  created_at: string;
};

const API_BASE =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://laravel.test:4430";

export default function EntityAuditPage() {
  const { id } = useParams<{ id: string }>();
  const [rows, setRows] = useState<Audit[]>([]);

  useEffect(() => {
    fetch(`${API_BASE}/api/item-entities/${id}/audits`, {
      credentials: "include",
    })
      .then((r) => r.json())
      .then(setRows);
  }, [id]);

  return (
    <div style={{ padding: 24 }}>
      <AdminNav />

      <h1>Entity Audit History</h1>

      {rows.map((a) => (
        <div
          key={a.id}
          style={{
            marginTop: 12,
            padding: 12,
            border: "1px solid #ddd",
            borderRadius: 6,
          }}
        >
          <div>
            <b>Date:</b> {a.created_at}
          </div>
          <div>
            <b>Decision:</b> {a.decision}
          </div>
          <div>
            <b>Confidence:</b> {a.confidence}
          </div>
          <pre style={{ marginTop: 8, fontSize: 12 }}>
            {JSON.stringify(a.payload, null, 2)}
          </pre>
        </div>
      ))}
    </div>
  );
}
