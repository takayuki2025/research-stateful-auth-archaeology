"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";

/* =========================
   Types
========================= */

type AnalysisRequestRow = {
  id: number;
  item_id: number;
  status: string;
  analysis_version: string;
  created_at: string;
  decision?: "approve" | "reject" | "system_approve" | null;
  decided_at?: string | null;
};

type ApiResponse = {
  requests: AnalysisRequestRow[];
};

/* =========================
   Fetcher
========================= */

const fetcher = async (url: string): Promise<ApiResponse> => {
  const res = await fetch(url, { credentials: "include" });
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body?.message ?? "Fetch failed");
  }
  return res.json();
};

/* =========================
   Page
========================= */

export default function AtlasRequestsPage() {
  const { shop_code } = useParams<{ shop_code: string }>();
  const { authReady, isAuthenticated, user } = useAuth();

  const isReviewer =
    user?.shop_roles?.some(
      (r) => r.shop_code === shop_code && ["owner", "manager"].includes(r.role)
    ) ?? false;

  const { data, error, isLoading } = useSWR<ApiResponse>(
    isReviewer ? `/api/shops/${shop_code}/atlas/requests` : null,
    fetcher
  );

  /* =========================
     Guards
  ========================= */

  if (!authReady || !isAuthenticated) {
    return <div className="p-6">認証確認中...</div>;
  }

  if (!isReviewer) {
    return <div className="p-6">アクセス権限がありません。</div>;
  }

  if (isLoading) {
    return <div className="p-6">読み込み中...</div>;
  }

  if (error) {
    return <div className="p-6 text-red-600">取得失敗：{error.message}</div>;
  }

  const requests = data?.requests ?? [];

  /* =========================
     Render
  ========================= */

  return (
    <div className="p-6 space-y-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Atlas 分析リクエスト</h1>

        {/* 🔍 Decision History link */}
        <Link
          href={`/shops/${shop_code}/dashboard/atlas/history`}
          className="text-sm text-blue-600 hover:underline"
        >
          判断履歴（History）
        </Link>
      </div>

      <table className="w-full border text-sm">
        <thead className="bg-gray-50">
          <tr>
            <th className="border p-2">ID</th>
            <th className="border p-2">Item</th>
            <th className="border p-2">Status</th>
            <th className="border p-2">Version</th>
            <th className="border p-2">Result</th>
          </tr>
        </thead>

        <tbody>
          {requests.map((r) => (
            <tr key={r.id}>
              <td className="border p-2">{r.id}</td>
              <td className="border p-2">{r.item_id}</td>
              <td className="border p-2 font-mono">{r.status}</td>
              <td className="border p-2">{r.analysis_version}</td>
              <td className="border p-2">
                {r.decision ? (
                  <Link
                    href={`/shops/${shop_code}/dashboard/atlas/history/${r.id}`}
                    className="underline font-semibold"
                  >
                    {r.decision === "approve" ? "Approved" : "Rejected"}
                  </Link>
                ) : r.status === "done" ? (
                  <Link
                    href={`/shops/${shop_code}/dashboard/atlas/review/${r.id}`}
                    className="text-blue-600 underline"
                  >
                    Review
                  </Link>
                ) : (
                  "-"
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
