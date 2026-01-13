"use client";

import { useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import useSWR from "swr";

/* =========================
   Types
========================= */

type AttributeKey = "brand" | "color" | "condition" | string;

type AttributeDecision = {
  value: string | null;
  confidence: number | null; // 0.0 - 1.0
  evidence?: string | null;
  alternatives?: { value: string; confidence?: number | null }[];
};

type ReviewPayload = {
  analysis_request_id: number;

  // 画面表示用（推奨）
  before: Record<AttributeKey, string | null>;
  after: Record<AttributeKey, string | null>;
  diff: Record<AttributeKey, { before: string | null; after: string | null }>;

  // 将来のAI接続前提
  attributes?: Record<AttributeKey, AttributeDecision>;

  // 全体 confidence（任意）
  overall_confidence?: number | null;

  // 現在ステータス（任意）
  status?: "pending" | "running" | "done" | "failed";
};

type ApiResponse = {
  request_id: number;
  review: ReviewPayload;
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

const postJson = async (url: string, body: any) => {
  const res = await fetch(url, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify(body),
  });
  if (!res.ok) {
    const data = await res.json().catch(() => ({}));
    throw new Error(data?.message ?? "Request failed");
  }
  return res.json().catch(() => ({}));
};

/* =========================
   UI helpers
========================= */

function pct(conf: number | null | undefined): string {
  if (conf === null || conf === undefined) return "-";
  return `${(conf * 100).toFixed(1)}%`;
}

function confClass(conf: number | null | undefined): string {
  if (conf === null || conf === undefined) return "text-gray-500";
  if (conf >= 0.9) return "text-green-700 font-semibold";
  if (conf >= 0.7) return "text-orange-700 font-semibold";
  return "text-red-700 font-semibold";
}

function changed(before: string | null, after: string | null): boolean {
  return (before ?? "") !== (after ?? "");
}

/* =========================
   Components
========================= */

function DiffRow(props: {
  label: string;
  before: string | null;
  after: string | null;
  confidence?: number | null;
  evidence?: string | null;
}) {
  const isChanged = changed(props.before, props.after);

  return (
    <div
      className={`border rounded ${isChanged ? "bg-yellow-50" : "bg-white"}`}
    >
      <div className="grid grid-cols-12 gap-2 p-3 items-center">
        <div className="col-span-3 font-semibold">{props.label}</div>

        <div className="col-span-4 text-gray-500">
          <div className="text-xs text-gray-400 mb-1">Before</div>
          <div className={isChanged ? "line-through" : ""}>
            {props.before ?? "-"}
          </div>
        </div>

        <div className="col-span-4">
          <div className="text-xs text-gray-400 mb-1">After</div>
          <div className={isChanged ? "font-bold text-green-800" : ""}>
            {props.after ?? "-"}
          </div>
        </div>

        <div className="col-span-1 text-right">
          <div className="text-[10px] text-gray-400">Conf</div>
          <div className={`text-sm ${confClass(props.confidence)}`}>
            {pct(props.confidence)}
          </div>
        </div>
      </div>

      {props.evidence ? (
        <div className="px-3 pb-3 text-xs text-gray-600">
          Evidence: {props.evidence}
        </div>
      ) : null}
    </div>
  );
}

/* =========================
   Page
========================= */

export default function AtlasReviewPage() {
  const router = useRouter();
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const reviewUrl = `/api/shops/${shop_code}/atlas/requests/${request_id}/review`;
  const decideUrl = `/api/shops/${shop_code}/atlas/requests/${request_id}/decide`;

  const { data, error, isLoading, mutate } = useSWR<ApiResponse>(
    reviewUrl,
    fetcher
  );

  const [mode, setMode] = useState<"view" | "edit_confirm">("view");
  const [saving, setSaving] = useState(false);

  // edit_confirm 用：Afterをベースに編集
  const [editValues, setEditValues] = useState<Record<string, string>>({});
  const [note, setNote] = useState("");

  const review = data?.review;

  const keys = useMemo(() => {
    if (!review) return [];
    // diff keys を優先
    const diffKeys = Object.keys(review.diff ?? {});
    const beforeKeys = Object.keys(review.before ?? {});
    const afterKeys = Object.keys(review.after ?? {});
    const merged = Array.from(
      new Set([...diffKeys, ...beforeKeys, ...afterKeys])
    );
    // 見やすさのため並び固定（存在するものだけ）
    const priority = ["brand", "color", "condition"];
    merged.sort((a, b) => {
      const pa = priority.indexOf(a);
      const pb = priority.indexOf(b);
      if (pa !== -1 && pb !== -1) return pa - pb;
      if (pa !== -1) return -1;
      if (pb !== -1) return 1;
      return a.localeCompare(b);
    });
    return merged;
  }, [review]);

  const onStartEditConfirm = () => {
    if (!review) return;
    const base: Record<string, string> = {};
    keys.forEach((k) => {
      const afterVal = review.after?.[k] ?? "";
      base[k] = afterVal ?? "";
    });
    setEditValues(base);
    setNote("");
    setMode("edit_confirm");
  };

  const submitDecision = async (payload: any) => {
    setSaving(true);
    try {
      await postJson(decideUrl, payload);
      await mutate(); // 最新反映
      router.push(`/shops/${shop_code}/dashboard/atlas/requests`);
    } catch (e: any) {
      alert(e?.message ?? "保存に失敗しました");
    } finally {
      setSaving(false);
    }
  };

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (error)
    return <div className="p-6 text-red-600">取得失敗：{error.message}</div>;
  if (!review)
    return <div className="p-6 text-red-600">review が取得できません</div>;

  const overall = review.overall_confidence ?? null;

  return (
    <div className="p-6 space-y-5">
      <div className="flex items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">Atlas Review #{request_id}</h1>
          <div className="text-sm text-gray-600 mt-1">
            status: <span className="font-mono">{review.status ?? "-"}</span>
            {"  "} / overall confidence:{" "}
            <span className={confClass(overall)}>{pct(overall)}</span>
          </div>
        </div>

        <div className="flex gap-2">
          <button
            className="border px-3 py-2 rounded hover:bg-gray-50"
            onClick={() =>
              router.push(`/shops/${shop_code}/dashboard/atlas/requests`)
            }
          >
            一覧へ戻る
          </button>

          <button
            className="border px-3 py-2 rounded hover:bg-gray-50"
            onClick={async () => {
              if (!confirm("この分析を再実行しますか？")) return;
              try {
                await postJson(
                  `/api/shops/${shop_code}/atlas/requests/${request_id}/replay`,
                  { version: "v3_ai", reason: "manual replay" }
                );
                alert("Replay を受け付けました（非同期）");
                router.push(`/shops/${shop_code}/dashboard/atlas/requests`);
              } catch (e: any) {
                alert(e?.message ?? "Replay に失敗しました");
              }
            }}
          >
            Replay（再分析）
          </button>
        </div>
      </div>

      {/* Mode switch */}
      <div className="flex items-center gap-2">
        <span className="text-sm text-gray-600">Mode:</span>
        <button
          className={`border px-3 py-1 rounded ${
            mode === "view" ? "bg-gray-100" : "hover:bg-gray-50"
          }`}
          onClick={() => setMode("view")}
        >
          View
        </button>
        <button
          className={`border px-3 py-1 rounded ${
            mode === "edit_confirm" ? "bg-gray-100" : "hover:bg-gray-50"
          }`}
          onClick={onStartEditConfirm}
        >
          Edit Confirm
        </button>
      </div>

      {/* Diff list */}
      <div className="space-y-3">
        {keys.map((k) => {
          const before = review.diff?.[k]?.before ?? review.before?.[k] ?? null;
          const after = review.diff?.[k]?.after ?? review.after?.[k] ?? null;
          const attr = review.attributes?.[k];
          return (
            <DiffRow
              key={k}
              label={k}
              before={before}
              after={after}
              confidence={attr?.confidence ?? null}
              evidence={attr?.evidence ?? null}
            />
          );
        })}
      </div>

      {/* Actions */}
      {mode === "view" ? (
        <div className="pt-4 border-t space-y-3">
          <div className="text-sm text-gray-600">
            AIの提案をそのまま採用する場合は Approve。修正する場合は Edit
            Confirm。
          </div>

          <div className="flex gap-2">
            <button
              disabled={saving}
              className="bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50"
              onClick={() =>
                submitDecision({
                  decision_type: "approve",
                  note: null,
                })
              }
            >
              Approve（採用）
            </button>

            <button
              disabled={saving}
              className="border px-4 py-2 rounded hover:bg-gray-50 disabled:opacity-50"
              onClick={onStartEditConfirm}
            >
              Edit & Approve（修正して確定）
            </button>

            <button
              disabled={saving}
              className="bg-red-600 text-white px-4 py-2 rounded disabled:opacity-50"
              onClick={() =>
                submitDecision({
                  decision_type: "reject",
                  note: null,
                })
              }
            >
              Reject（却下）
            </button>
          </div>
        </div>
      ) : (
        <div className="pt-4 border-t space-y-4">
          <div className="text-sm text-gray-700">
            AI提案をベースに修正して確定します。確定すると decision ledger
            に保存されます。
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            {keys.map((k) => (
              <div key={k} className="border rounded p-3 space-y-1">
                <div className="text-sm font-semibold">{k}</div>
                <div className="text-xs text-gray-500">
                  AI after: {review.after?.[k] ?? "-"}
                </div>
                <input
                  className="border rounded w-full px-2 py-1"
                  value={editValues[k] ?? ""}
                  onChange={(e) =>
                    setEditValues((p) => ({ ...p, [k]: e.target.value }))
                  }
                />
              </div>
            ))}
          </div>

          <div className="space-y-1">
            <div className="text-sm font-semibold">Note（任意）</div>
            <textarea
              className="border rounded w-full p-2"
              rows={3}
              value={note}
              onChange={(e) => setNote(e.target.value)}
              placeholder="編集の理由・根拠など"
            />
          </div>

          <div className="flex gap-2">
            <button
              disabled={saving}
              className="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50"
              onClick={() =>
                submitDecision({
                  decision_type: "edit_confirm",
                  note: note || null,
                  after_snapshot: editValues,
                })
              }
            >
              保存して確定（Edit Confirm）
            </button>

            <button
              disabled={saving}
              className="border px-4 py-2 rounded hover:bg-gray-50 disabled:opacity-50"
              onClick={() => setMode("view")}
            >
              キャンセル
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
