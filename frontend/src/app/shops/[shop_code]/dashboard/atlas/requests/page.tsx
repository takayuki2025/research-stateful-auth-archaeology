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

  // ★ Cフェーズ（判断済み）
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

  /* =========================
     Authorization
  ========================= */

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

  const requests = Array.isArray(data?.requests) ? data.requests : [];

  if (requests.length === 0) {
    return (
      <div className="p-6 space-y-4">
        <h1 className="text-2xl font-semibold">Atlas 分析リクエスト</h1>
        <p className="text-gray-600">現在、分析リクエストはありません。</p>
      </div>
    );
  }

  /* =========================
     Render
  ========================= */

  return (
    <div className="p-6 space-y-4">
      <h1 className="text-2xl font-semibold">Atlas 分析リクエスト</h1>

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

              {/* =========================
                  Result column
                  B / C フェーズ完全分離
              ========================= */}
              <td className="border p-2">
                {/* Cフェーズ：判断済み → 履歴へ */}
                {r.decision ? (
                  <Link
                    href={`/shops/${shop_code}/dashboard/atlas/history/${r.id}`}
                    className={
                      r.decision === "approve"
                        ? "text-green-600 font-semibold underline"
                        : "text-red-600 font-semibold underline"
                    }
                  >
                    {r.decision === "approve" ? "Approved" : "Rejected"}
                  </Link>
                ) : /* Bフェーズ：未判断 & done → Review */ r.status ===
                  "done" ? (
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
