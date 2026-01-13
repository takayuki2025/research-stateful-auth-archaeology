"use client";

import { useParams, useRouter } from "next/navigation";
import useSWR from "swr";

/* =========================
   Types
========================= */

type DecisionHistoryItem = {
  id: number;
  request_id: number;
  decision_type: "approve" | "edit_confirm" | "reject";
  decided_by: {
    id: number;
    name: string;
    role: string;
  } | null;
  note: string | null;
  created_at: string;
};

type ApiResponse = {
  histories: DecisionHistoryItem[];
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

export default function AtlasDecisionHistoryPage() {
  const router = useRouter();
  const { shop_code } = useParams<{ shop_code: string }>();

  const apiUrl = `/api/shops/${shop_code}/atlas/history`;

  const { data, error, isLoading } = useSWR<ApiResponse>(apiUrl, fetcher);

  if (isLoading) {
    return <div className="p-6">読み込み中...</div>;
  }

  if (error) {
    return <div className="p-6 text-red-600">取得失敗：{error.message}</div>;
  }

  if (!data || data.histories.length === 0) {
    return <div className="p-6">判断履歴はまだありません。</div>;
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Atlas Decision History</h1>

        <button
          className="border px-4 py-2 rounded hover:bg-gray-50"
          onClick={() =>
            router.push(`/shops/${shop_code}/dashboard/atlas/requests`)
          }
        >
          Review一覧へ戻る
        </button>
      </div>

      <table className="w-full border">
        <thead>
          <tr className="bg-gray-100 text-sm">
            <th className="p-2 border">Request ID</th>
            <th className="p-2 border">Decision</th>
            <th className="p-2 border">Decided By</th>
            <th className="p-2 border">Note</th>
            <th className="p-2 border">At</th>
            <th className="p-2 border"></th>
          </tr>
        </thead>

        <tbody>
          {data.histories.map((h) => (
            <tr key={h.id} className="border-t text-sm">
              <td className="p-2 border font-mono">#{h.request_id}</td>

              <td className="p-2 border">
                <span
                  className={
                    h.decision_type === "approve"
                      ? "text-green-700 font-semibold"
                      : h.decision_type === "edit_confirm"
                        ? "text-blue-700 font-semibold"
                        : "text-red-700 font-semibold"
                  }
                >
                  {h.decision_type}
                </span>
              </td>

              <td className="p-2 border">
                {h.decided_by
                  ? `${h.decided_by.name} (${h.decided_by.role})`
                  : "system"}
              </td>

              <td className="p-2 border text-gray-600">{h.note ?? "—"}</td>

              <td className="p-2 border">
                {new Date(h.created_at).toLocaleString()}
              </td>

              <td className="p-2 border text-right">
                <button
                  className="text-blue-600 hover:underline"
                  onClick={() =>
                    router.push(
                      `/shops/${shop_code}/dashboard/atlas/history/${h.request_id}`
                    )
                  }
                >
                  詳細
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
