"use client";

import { useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import useSWR, { mutate } from "swr";

/* =========================================================
   Types
========================================================= */

type AttrKey = "brand" | "color" | "condition";

type AttrValue = {
  value: string | null;
  confidence?: number | null;
  confidence_version?: string | null;
  source?: "rule" | "gpt" | "vision" | "ocr" | "manual" | "unknown";
};

type Snapshot = Partial<Record<AttrKey, AttrValue>>;

type ReviewSourceResponse = {
  analysis_request_id: number;
  item_id: number;
  before: Snapshot | null;
  after: Snapshot | null; // analyzer結果（候補）
  latest_decision?: {
    decision_type: string;
    decided_at: string;
    decided_by_type: string;
    decided_by?: number | null;
    note?: string | null;
  } | null;
};

type DecideRequestBody = {
  decisionType: "approve" | "reject" | "edit_confirm" | "manual_override";
  afterSnapshot?: Snapshot | null;
  note?: string | null;
};

type DecideResponse = { status: "ok" | "accepted" };

/* =========================================================
   Fetcher
========================================================= */

const fetcher = async (url: string) => {
  const res = await fetch(url, { credentials: "include" });
  if (!res.ok) {
    const txt = await res.text().catch(() => "");
    throw new Error(txt || "Fetch failed");
  }
  return res.json();
};

/* =========================================================
   Helpers
========================================================= */

const ATTRS: { key: AttrKey; label: string; description: string }[] = [
  { key: "brand", label: "Brand", description: "ブランド名（正規化）" },
  { key: "color", label: "Color", description: "カラー（正規化）" },
  { key: "condition", label: "Condition", description: "状態（正規化）" },
];

function fmtConfidence(v?: number | null) {
  if (v === null || v === undefined) return "-";
  const n = Math.round(v * 100);
  return `${n}%`;
}

function confidenceClass(v?: number | null) {
  if (v === null || v === undefined)
    return "bg-gray-100 text-gray-700 border-gray-200";
  if (v >= 0.8) return "bg-green-50 text-green-700 border-green-200";
  if (v >= 0.7) return "bg-yellow-50 text-yellow-800 border-yellow-200";
  return "bg-red-50 text-red-700 border-red-200";
}

function diffState(before?: AttrValue | null, after?: AttrValue | null) {
  const b = (before?.value ?? "").trim();
  const a = (after?.value ?? "").trim();
  if (!b && !a) return "none";
  if (b === a) return "same";
  if (!b && a) return "added";
  if (b && !a) return "removed";
  return "changed";
}

function labelForDiff(s: ReturnType<typeof diffState>) {
  switch (s) {
    case "same":
      return { text: "Same", cls: "bg-gray-50 text-gray-700 border-gray-200" };
    case "added":
      return { text: "Added", cls: "bg-blue-50 text-blue-700 border-blue-200" };
    case "removed":
      return {
        text: "Removed",
        cls: "bg-orange-50 text-orange-700 border-orange-200",
      };
    case "changed":
      return {
        text: "Changed",
        cls: "bg-purple-50 text-purple-700 border-purple-200",
      };
    default:
      return { text: "None", cls: "bg-gray-50 text-gray-700 border-gray-200" };
  }
}

/* =========================================================
   Page
========================================================= */

export default function AtlasReviewPage() {
  const router = useRouter();
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  // ---- endpoints（必要ならここだけ変更）----
  const ENDPOINT = {
    review: `/api/shops/${shop_code}/atlas/requests/${request_id}/review`,
    decide: `/api/shops/${shop_code}/atlas/requests/${request_id}/decide`,
    back: `/shops/${shop_code}/dashboard/atlas/requests`,
  };

  const { data, error, isLoading } = useSWR<ReviewSourceResponse>(
    ENDPOINT.review,
    fetcher
  );

  // UI State
  const [note, setNote] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Manual edit_confirm / manual_override 用の「編集値」
  const [edit, setEdit] = useState<Snapshot>({});
  const [mode, setMode] = useState<
    "approve" | "edit_confirm" | "manual_override" | "reject"
  >("approve");

  const before = data?.before ?? null;
  const after = data?.after ?? null;

  // 初回に analyzer(after) を編集フォームへ流し込み（安全に一度だけ）
  const initialEdit = useMemo(() => {
    const base: Snapshot = {};
    for (const a of ATTRS) {
      const v = after?.[a.key] ?? null;
      if (v) base[a.key] = { ...v };
    }
    return base;
  }, [after]);

  useMemo(() => {
    // edit が空なら初期化
    if (Object.keys(edit).length === 0 && Object.keys(initialEdit).length > 0) {
      setEdit(initialEdit);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialEdit]);

  const rows = useMemo(() => {
    return ATTRS.map((a) => {
      const b = before?.[a.key] ?? null;
      const ai = after?.[a.key] ?? null;
      const e = edit?.[a.key] ?? null;

      const state = diffState(b, ai);
      const badge = labelForDiff(state);

      // 表示上の “After” は mode によって変える：
      // approve/reject なら analyzer(after)
      // edit_confirm/manual_override なら edit(人手)
      const shownAfter =
        mode === "edit_confirm" || mode === "manual_override" ? e : ai;

      // 最大confidenceは Policyに渡す想定 → UIとしても危険域表示に使う
      const conf = shownAfter?.confidence ?? ai?.confidence ?? null;

      return {
        key: a.key,
        label: a.label,
        description: a.description,
        before: b,
        ai,
        edit: e,
        shownAfter,
        conf,
        diffBadge: badge,
      };
    });
  }, [before, after, edit, mode]);

  const maxConfidence = useMemo(() => {
    const vals = rows
      .map((r) => r.shownAfter?.confidence)
      .filter((v): v is number => typeof v === "number");
    if (vals.length === 0) return null;
    return Math.max(...vals);
  }, [rows]);

  const needsCautionPopup = useMemo(() => {
    // 要件：confidence >= 0.7 の手動入力反映時に注意ポップアップ
    // edit_confirm/manual_override のときにだけ効く
    if (!(mode === "edit_confirm" || mode === "manual_override")) return false;
    if (maxConfidence === null) return false;
    return maxConfidence >= 0.7;
  }, [mode, maxConfidence]);

  async function submitDecision(body: DecideRequestBody) {
    setIsSubmitting(true);
    try {
      const res = await fetch(ENDPOINT.decide, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(body),
      });
      if (!res.ok) {
        const txt = await res.text().catch(() => "");
        throw new Error(txt || "Decide failed");
      }
      await res.json().catch(() => ({}) as DecideResponse);
      await mutate(ENDPOINT.review);
      router.push(ENDPOINT.back);
    } finally {
      setIsSubmitting(false);
    }
  }

  function buildAfterSnapshotForHuman(): Snapshot {
    // 人手決定の afterSnapshot：edit に入っている value を採用
    // confidence / version は「manual」へ寄せる（サーバ側で最終正規化してもOK）
    const out: Snapshot = {};
    for (const a of ATTRS) {
      const v = edit?.[a.key]?.value ?? null;
      if (v !== null && v !== undefined && String(v).trim() !== "") {
        out[a.key] = {
          value: String(v).trim(),
          confidence: edit?.[a.key]?.confidence ?? null,
          confidence_version: edit?.[a.key]?.confidence_version ?? "v3_manual",
          source: "manual",
        };
      }
    }
    return out;
  }

  function ensureEditReadyOrThrow() {
    const snap = buildAfterSnapshotForHuman();
    if (Object.keys(snap).length === 0) {
      throw new Error(
        "after_snapshot is required for edit_confirm/manual_override."
      );
    }
    return snap;
  }

  /* =========================================================
     Render
  ========================================================= */

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (error)
    return (
      <div className="p-6 text-red-600">
        取得失敗: {(error as Error).message}
      </div>
    );
  if (!data) return <div className="p-6">データがありません</div>;

  return (
    <div className="p-6 space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold">Atlas Review #{request_id}</h1>
          <div className="text-sm text-gray-600 mt-1">
            Item: <span className="font-medium">{data.item_id}</span>
            <span className="mx-2">·</span>
            Request:{" "}
            <span className="font-medium">{data.analysis_request_id}</span>
          </div>
          {data.latest_decision && (
            <div className="text-xs text-gray-500 mt-2">
              Latest decision:{" "}
              <span className="font-medium">
                {data.latest_decision.decision_type}
              </span>
              <span className="mx-2">·</span>
              {data.latest_decision.decided_at}
              {data.latest_decision.note ? (
                <>
                  <span className="mx-2">·</span>
                  {data.latest_decision.note}
                </>
              ) : null}
            </div>
          )}
        </div>

        <button
          className="text-blue-600 underline text-sm"
          onClick={() => router.push(ENDPOINT.back)}
        >
          ← 一覧へ戻る
        </button>
      </div>

      {/* Mode switch */}
      <div className="border rounded-lg p-4 space-y-3">
        <div className="text-sm font-semibold">操作モード</div>
        <div className="flex flex-wrap gap-2">
          <ModePill
            label="Approve（採用）"
            active={mode === "approve"}
            onClick={() => setMode("approve")}
          />
          <ModePill
            label="Edit Confirm（修正して採用）"
            active={mode === "edit_confirm"}
            onClick={() => setMode("edit_confirm")}
          />
          <ModePill
            label="Manual Override（手動上書き）"
            active={mode === "manual_override"}
            onClick={() => setMode("manual_override")}
          />
          <ModePill
            label="Reject（棄却）"
            active={mode === "reject"}
            onClick={() => setMode("reject")}
          />
        </div>

        {/* Confidence summary */}
        <div className="flex items-center gap-3 text-sm">
          <span className="text-gray-600">Max confidence</span>
          <span
            className={`px-2 py-1 rounded border ${confidenceClass(maxConfidence)}`}
          >
            {fmtConfidence(maxConfidence)}
          </span>
          {needsCautionPopup && (
            <span className="text-xs text-gray-500">
              ※ confidence 70%以上の手動反映は「業務改善通知」が送られる想定です
            </span>
          )}
        </div>
      </div>

      {/* Diff table */}
      <div className="border rounded-lg overflow-hidden">
        <div className="grid grid-cols-12 bg-gray-50 text-xs text-gray-600 px-4 py-2">
          <div className="col-span-2 font-semibold">Attribute</div>
          <div className="col-span-4 font-semibold">Before（現行）</div>
          <div className="col-span-4 font-semibold">
            {mode === "edit_confirm" || mode === "manual_override"
              ? "After（手動）"
              : "After（解析）"}
          </div>
          <div className="col-span-2 font-semibold text-right">Confidence</div>
        </div>

        {rows.map((r) => (
          <div
            key={r.key}
            className="grid grid-cols-12 px-4 py-3 border-t items-start gap-2"
          >
            <div className="col-span-2">
              <div className="font-semibold">{r.label}</div>
              <div className="text-xs text-gray-500">{r.description}</div>
              <div
                className={`inline-flex mt-2 px-2 py-0.5 rounded border text-xs ${r.diffBadge.cls}`}
              >
                {r.diffBadge.text}
              </div>
            </div>

            <div className="col-span-4">
              <ValueCard
                value={r.before?.value ?? null}
                meta={renderMeta(r.before)}
              />
            </div>

            <div className="col-span-4 space-y-2">
              {mode === "edit_confirm" || mode === "manual_override" ? (
                <div className="space-y-1">
                  <input
                    className="w-full border rounded px-3 py-2 text-sm"
                    placeholder="手動で入力（例: Apple）"
                    value={r.edit?.value ?? ""}
                    onChange={(e) => {
                      const v = e.target.value;
                      setEdit((prev) => ({
                        ...prev,
                        [r.key]: {
                          ...(prev[r.key] ?? {}),
                          value: v,
                          // 手動入力時は confidence を null でもOK。将来は human_confidence などに拡張可
                          confidence: prev[r.key]?.confidence ?? null,
                          confidence_version:
                            prev[r.key]?.confidence_version ?? "v3_manual",
                          source: "manual",
                        },
                      }));
                    }}
                  />
                  <div className="text-xs text-gray-500">
                    解析候補:{" "}
                    <span className="font-medium">{r.ai?.value ?? "-"}</span>
                    <span className="mx-2">·</span>
                    conf {fmtConfidence(r.ai?.confidence)}
                  </div>
                </div>
              ) : (
                <ValueCard
                  value={r.ai?.value ?? null}
                  meta={renderMeta(r.ai)}
                />
              )}
            </div>

            <div className="col-span-2 flex justify-end">
              <span
                className={`px-2 py-1 rounded border text-xs ${confidenceClass(r.conf)}`}
              >
                {fmtConfidence(r.conf)}
              </span>
            </div>
          </div>
        ))}
      </div>

      {/* Note */}
      <div className="border rounded-lg p-4 space-y-2">
        <div className="text-sm font-semibold">Note（監査・学習用）</div>
        <textarea
          className="w-full border rounded px-3 py-2 text-sm min-h-[90px]"
          placeholder="判断理由・補足（例: 型番表記ゆれのため手動修正）"
          value={note}
          onChange={(e) => setNote(e.target.value)}
        />
        <div className="text-xs text-gray-500">
          ※ manual_override / edit_confirm は after_snapshot
          と共に保存され、学習データ抽出の対象になります
        </div>
      </div>

      {/* Actions */}
      <div className="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div className="text-xs text-gray-500">
          操作: <span className="font-medium">{mode}</span>
        </div>

        <div className="flex gap-2">
          <button
            className="border px-4 py-2 rounded hover:bg-gray-50"
            onClick={() => router.push(ENDPOINT.back)}
            disabled={isSubmitting}
          >
            キャンセル
          </button>

          <button
            className="px-4 py-2 rounded bg-black text-white hover:opacity-90 disabled:opacity-50"
            disabled={isSubmitting}
            onClick={async () => {
              try {
                if (mode === "approve") {
                  await submitDecision({
                    decisionType: "approve",
                    afterSnapshot: null,
                    note: note || null,
                  });
                  return;
                }

                if (mode === "reject") {
                  await submitDecision({
                    decisionType: "reject",
                    afterSnapshot: null,
                    note: note || null,
                  });
                  return;
                }

                // edit_confirm / manual_override
                const snap = ensureEditReadyOrThrow();

                // 要件：confidence>=0.7 の手動反映時は注意ポップアップ
                if (needsCautionPopup) {
                  const ok = confirm(
                    "confidence 70%以上の手動反映です。業務改善のため管理者へ通知されます。続行しますか？"
                  );
                  if (!ok) return;
                }

                await submitDecision({
                  decisionType: mode,
                  afterSnapshot: snap,
                  note: note || null,
                });
              } catch (e) {
                alert((e as Error).message);
              }
            }}
          >
            {isSubmitting ? "送信中..." : "確定して保存"}
          </button>
        </div>
      </div>
    </div>
  );
}

/* =========================================================
   UI parts
========================================================= */

function ModePill({
  label,
  active,
  onClick,
}: {
  label: string;
  active: boolean;
  onClick: () => void;
}) {
  return (
    <button
      className={`px-3 py-1.5 rounded-full border text-sm ${
        active
          ? "bg-black text-white border-black"
          : "bg-white hover:bg-gray-50"
      }`}
      onClick={onClick}
      type="button"
    >
      {label}
    </button>
  );
}

function ValueCard({
  value,
  meta,
}: {
  value: string | null;
  meta: React.ReactNode;
}) {
  return (
    <div className="border rounded p-3 bg-white">
      <div className="text-sm font-medium">
        {value && value.trim() ? (
          value
        ) : (
          <span className="text-gray-400">-</span>
        )}
      </div>
      <div className="text-xs text-gray-500 mt-1">{meta}</div>
    </div>
  );
}

function renderMeta(v?: AttrValue | null) {
  if (!v) return <span>-</span>;
  const parts: string[] = [];
  if (v.source) parts.push(`src:${v.source}`);
  if (v.confidence_version) parts.push(`ver:${v.confidence_version}`);
  return <span>{parts.length ? parts.join(" · ") : "-"}</span>;
}
