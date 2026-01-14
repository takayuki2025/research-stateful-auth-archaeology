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

  before: Record<AttributeKey, string | null>;
  after: Record<AttributeKey, string | null>;
  diff: Record<AttributeKey, { before: string | null; after: string | null }>;

  attributes?: Record<AttributeKey, AttributeDecision>;
  overall_confidence?: number | null;
  status?: "pending" | "running" | "done" | "failed";
};

type ApiResponse = ReviewPayload & { requestId: number };

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
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
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

      {props.evidence && (
        <div className="px-3 pb-3 text-xs text-gray-600">
          Evidence: {props.evidence}
        </div>
      )}
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

  // ✅ number に正規化（唯一の真実）
  const requestId = Number(request_id);

  // ✅ NaN ガード（Hooksの後）
  const isValidRequestId = Number.isFinite(requestId);

  // ✅ URL は number を使う
  const reviewUrl = isValidRequestId
    ? `/api/shops/${shop_code}/atlas/requests/${requestId}/review`
    : null;

  const decideUrl = isValidRequestId
    ? `/api/shops/${shop_code}/atlas/requests/${requestId}/decide`
    : null;

  const { data, error, isLoading, mutate } = useSWR<ApiResponse>(
    reviewUrl,
    fetcher
  );

  const [mode, setMode] = useState<"view" | "edit_confirm">("view");
  const [saving, setSaving] = useState(false);
  const [editValues, setEditValues] = useState<Record<string, string>>({});
  const [note, setNote] = useState("");

  const review = data ?? null;

  const keys = useMemo(() => {
    if (!review) return [];
    const merged = new Set<string>([
      ...Object.keys(review.diff ?? {}),
      ...Object.keys(review.before ?? {}),
      ...Object.keys(review.after ?? {}),
    ]);

    const priority = ["brand", "color", "condition"];
    return Array.from(merged).sort((a, b) => {
      const pa = priority.indexOf(a);
      const pb = priority.indexOf(b);
      if (pa !== -1 && pb !== -1) return pa - pb;
      if (pa !== -1) return -1;
      if (pb !== -1) return 1;
      return a.localeCompare(b);
    });
  }, [review]);

  const onStartEditConfirm = () => {
    if (!review) return;
    const base: Record<string, string> = {};
    keys.forEach((k) => {
      base[k] = review.after?.[k] ?? "";
    });
    setEditValues(base);
    setNote("");
    setMode("edit_confirm");
  };

  const submitDecision = async (payload: any) => {
    if (!decideUrl) return;
    setSaving(true);
    try {
      await postJson(decideUrl, payload);
      await mutate();
      router.push(`/shops/${shop_code}/dashboard/atlas/requests`);
    } catch (e: any) {
      alert(e?.message ?? "保存に失敗しました");
    } finally {
      setSaving(false);
    }
  };

  /* =========================
     Render guards
  ========================= */

  if (!isValidRequestId) {
    return <div className="p-6 text-red-600">Invalid request_id</div>;
  }

  if (isLoading) {
    return <div className="p-6">読み込み中...</div>;
  }

  if (error) {
    return <div className="p-6 text-red-600">取得失敗：{error.message}</div>;
  }

  if (!review) {
    return <div className="p-6 text-red-600">review が取得できません</div>;
  }

  const overall = review.overall_confidence ?? null;

  /* =========================
     UI
  ========================= */

  return (
    <div className="p-6 space-y-5">
      <h1 className="text-2xl font-semibold">Atlas Review #{requestId}</h1>

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
    </div>
  );
}
