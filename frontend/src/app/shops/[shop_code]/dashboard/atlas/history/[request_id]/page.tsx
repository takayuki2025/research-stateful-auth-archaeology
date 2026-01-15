"use client";

import { useParams, useRouter } from "next/navigation";
import useSWR from "swr";

/* =========================
   Types
========================= */

type Decision = {
  id: number;
  decision_type: "approve" | "reject" | "system_approve";
  decision_reason?: string | null;
  note?: string | null;
  decided_by_type: "human" | "system";
  decided_by?: number | null;
  decided_at: string;
};

type ApiResponse = {
  request_id: number;
  decisions: Decision[];
};

/* =========================
   Fetcher
========================= */

const fetcher = async (url: string): Promise<ApiResponse> => {
  const res = await fetch(url, { credentials: "include" });
  if (!res.ok) throw new Error("Fetch failed");
  return res.json();
};

/* =========================
   Page
========================= */

export default function AtlasDecisionHistoryDetailPage() {
  const router = useRouter();
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const { data, error, isLoading } = useSWR<ApiResponse>(
    `/api/shops/${shop_code}/atlas/requests/${request_id}/history`,
    fetcher
  );

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (error) return <div className="p-6 text-red-600">取得失敗</div>;

  const decisions = data?.decisions ?? [];

  return (
    <div className="p-6 space-y-6">
      <h1 className="text-2xl font-semibold">
        Atlas Decision History #{request_id}
      </h1>

      {/* 履歴 */}
      {decisions.length === 0 ? (
        <div className="text-gray-500">判断履歴はありません。</div>
      ) : (
        <div className="space-y-4">
          {decisions.map((d) => (
            <div key={d.id} className="border rounded p-4 space-y-1">
              <div className="font-semibold">
                Decision:{" "}
                <span
                  className={
                    d.decision_type === "approve"
                      ? "text-green-600"
                      : "text-red-600"
                  }
                >
                  {d.decision_type}
                </span>
              </div>

              <div className="text-sm text-gray-600">
                Reason: {d.decision_reason ?? "-"}
              </div>

              {d.note && <div className="text-sm">Note: {d.note}</div>}

              <div className="text-xs text-gray-500">
                By: {d.decided_by_type} / At: {d.decided_at}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* =========================
         Dフェーズ：Replay
      ========================= */}
      <div className="pt-6 border-t space-y-2">
        <button
          className="border px-4 py-2 rounded hover:bg-gray-50"
          onClick={async () => {
            if (!confirm("この分析を再実行しますか？")) return;

            const res = await fetch(
              `/api/shops/${shop_code}/atlas/requests/${request_id}/replay`,
              {
                method: "POST",
                credentials: "include",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ version: "v3_ai" }),
              }
            );

            if (res.ok) {
              alert("Replay を受け付けました（非同期）");
              router.push(`/shops/${shop_code}/dashboard/atlas/requests`);
            } else {
              alert("Replay に失敗しました");
            }
          }}
        >
          Replay（再分析）
        </button>

        <div className="text-xs text-gray-500">
          ※ 元の判断・履歴は変更されません
        </div>
      </div>

      <button
        className="text-blue-600 underline"
        onClick={() =>
          router.push(`/shops/${shop_code}/dashboard/atlas/requests`)
        }
      >
        ← 一覧へ戻る
      </button>
    </div>
  );
}
