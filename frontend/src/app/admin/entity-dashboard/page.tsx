"use client";

import { useEffect, useState } from "react";
import { AdminNav } from "@/components/layout/AdminNav";

type KPI = {
  total: number;
  needs_review: number;
  auto_accept: number;
  rejected: number;
  approval_rate: number;
};

const API_BASE =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://laravel.test:4430";

export default function DashboardPage() {
  const [kpi, setKpi] = useState<KPI | null>(null);

  useEffect(() => {
    fetch(`${API_BASE}/api/entity-kpis`, { credentials: "include" })
      .then((r) => r.json())
      .then(setKpi);
  }, []);

  if (!kpi) return <p>Loading...</p>;

  return (
    <div style={{ padding: 24 }}>
      <AdminNav />

      <h1>AI Operation Dashboard</h1>

      <ul style={{ marginTop: 16 }}>
        <li>✔ Total analyzed: {kpi.total}</li>
        <li>⚠ Needs review: {kpi.needs_review}</li>
        <li>✅ Auto accept: {kpi.auto_accept}</li>
        <li>❌ Rejected: {kpi.rejected}</li>
        <li>📈 Approval rate: {kpi.approval_rate}%</li>
      </ul>
    </div>
  );
}
