"use client";

import React, { useMemo, useState, useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import useSWR, { mutate } from "swr";
import { useResolveEntities } from "@/ui/atlas/hooks/useResolveEntities";
import type {
  AtlasTokens,
  ConfidenceMap,
  AtlasAttributes,
  AfterSnapshot,
} from "@/ui/atlas/types";

/* =========================================================
   Types
========================================================= */

type AttrKey = "brand" | "color" | "condition";

type AttrValue = {
  value: string | null;
  confidence?: number | null;
  confidence_version?: string | null;
  source?: "ai" | "manual" | "rule" | "gpt" | "vision" | "ocr" | "unknown";
};

type Snapshot = Partial<Record<AttrKey, AttrValue>>;

export type ReviewSourceResponse = {
  analysis_request_id: number;
  item_id: number;

  learning?: string | null;
  input_snapshot?: Record<string, unknown> | null;

  /* ========= v3 core ========= */
  tokens?: AtlasTokens | null;
  attributes?: AtlasAttributes | null;
  confidence_map?: ConfidenceMap | null;

  before: Snapshot | null;
  after: Snapshot | null;

  /* ========= decision ========= */
  latest_decision?: {
    decision_type: string;
    decided_at: string;
    decided_by_type: string;
    decided_by?: number | null;
    note?: string | null;
  } | null;

  /* ========= raw ========= */
  beforeParsed?: {
    name?: string | null;
    description?: string | null;
    brand?: string | null;
    color?: string | null;
    condition?: string | null;
  } | null;
};

type DecideRequestBody = {
  decision_type: "approve" | "reject" | "edit_confirm" | "manual_override";

  resolvedEntities?: {
    brand_entity_id?: number | null;
    condition_entity_id?: number | null;
    color_entity_id?: number | null;
  };

  beforeParsed?: {
    brand?: string | null;
    color?: string | null;
    condition?: string | null;
  };

  afterParsed?: {
    brand?: string | null;
    color?: string | null;
    condition?: string | null;
  };

  after_snapshot?: AfterSnapshot;
  note?: string | null;
};

type DecideResponse = { status: "ok" | "accepted" };

type ResolvedEntitiesForBackend = {
  brand_entity_id?: number | null;
  condition_entity_id?: number | null;
  color_entity_id?: number | null;
};

type EntityOption = {
  id: number;
  canonical_name: string;
};

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

function normalizeSnapshot(
  attributes: AtlasAttributes | null | undefined,
  tokens: AtlasTokens | null | undefined,
  confidenceMap: ConfidenceMap | null | undefined,
): Snapshot | null {
  const out: Snapshot = {};

  (["brand", "color", "condition"] as const).forEach((key: AttrKey) => {
    // ① canonical（attributes優先）
    const canonical = attributes?.[key]?.value ?? null;

    if (canonical && String(canonical).trim() !== "") {
      out[key] = {
        value: String(canonical),
        confidence: confidenceMap?.[key] ?? null,
        source: "ai",
        confidence_version: "v3_ai_canonical",
      };
      return;
    }

    // ② fallback：raw token
    const token = tokens?.[key]?.[0] ?? null;
    if (token && String(token).trim() !== "") {
      out[key] = {
        value: String(token),
        confidence: confidenceMap?.[key] ?? null,
        source: "ai",
        confidence_version: "v3_ai_token",
      };
    }
  });

  return Object.keys(out).length ? out : null;
}

function snapshotFromAI(after: Snapshot | null): Snapshot {
  const base: Snapshot = {};
  if (!after) return base;

  (["brand", "color", "condition"] as const).forEach((k) => {
    const v = after[k]?.value ?? null;
    if (v && v.trim() !== "") {
      base[k] = {
        value: v,
        source: "ai",
        confidence: after[k]?.confidence ?? null,
        confidence_version: after[k]?.confidence_version ?? "v3_ai",
      };
    }
  });

  return base;
}

function buildAfterSnapshot(edit: Snapshot): AfterSnapshot {
  const out: AfterSnapshot = {};

  (["brand", "condition", "color"] as const).forEach((key) => {
    const v = edit[key];
    if (v?.value && v.value.trim() !== "") {
      out[key] = {
        value: v.value.trim(),
        source: "manual",
        confidence: v.confidence ?? null,
      };
    }
  });

  if (Object.keys(out).length === 0) {
    throw new Error("after_snapshot is empty. 手動入力がありません。");
  }

  return out;
}



function rawTokenFor(key: AttrKey, tokens?: AtlasTokens | null): string | null {
  const t = tokens?.[key]?.[0] ?? null;
  if (!t) return null;
  const s = String(t).trim();
  return s ? s : null;
}

/**
 * v3 FIXED:
 * - edit_confirm: canonical entity 選択 → resolvedEntities(entity_id) を送る
 * - manual_override: 自由入力 → resolvedEntities は null 固定、after_snapshot を送る
 * - approve: resolveBeforeDecide()（after(AI)から推定）→ resolvedEntities を送る。after_snapshotは送らない
 * - reject: resolvedEntities/after_snapshot は送らない（noteのみ）
 */

/* =========================================================
   Entity options hook
========================================================= */

function useEntityOptions(
  kind: "brands" | "conditions" | "colors",
  enabled: boolean
) {
  const url = enabled ? `/api/entities/${kind}` : null;
  return useSWR<EntityOption[]>(url, fetcher);
}

function findIdByCanonicalName(
  options: EntityOption[],
  name: string | null | undefined
): number | null {
  if (!name) return null;
  const hit = options.find((o) => o.canonical_name === name);
  return hit?.id ?? null;
}

/* =========================================================
   Page
========================================================= */

export default function AtlasReviewPage() {
  const router = useRouter();
  const { shop_code, request_id } = useParams() as {
    shop_code: string;
    request_id: string;
  };

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

  const { resolve } = useResolveEntities(shop_code, request_id);

  // UI State
  const [note, setNote] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Manual edit_confirm / manual_override 用の「編集値」
  const [edit, setEdit] = useState<Snapshot>({});
  const [mode, setMode] = useState<
    "approve" | "edit_confirm" | "manual_override" | "reject"
  >("approve");

  // edit_confirm 用：選択された canonical entity id（backendへ送る）
  const [selectedIds, setSelectedIds] = useState<ResolvedEntitiesForBackend>({
    brand_entity_id: null,
    condition_entity_id: null,
    color_entity_id: null,
  });

  const editConfirmEnabled = mode === "edit_confirm";

  const { data: brandOptions = [] } = useEntityOptions(
    "brands",
    editConfirmEnabled
  );
  const { data: conditionOptions = [] } = useEntityOptions(
    "conditions",
    editConfirmEnabled
  );
  const { data: colorOptions = [] } = useEntityOptions(
    "colors",
    editConfirmEnabled
  );

  const before = useMemo(() => {
    const rawBefore = data?.before as any;

    const isValueObject =
      rawBefore &&
      typeof rawBefore === "object" &&
      ["brand", "color", "condition"].some(
        (k) =>
          typeof rawBefore?.[k] === "object" &&
          rawBefore?.[k]?.value !== undefined
      );

    if (isValueObject) {
      return rawBefore as Snapshot;
    }

    // ⬇️ ここで初めて beforeParsed を使う
    if (data?.beforeParsed) {
      const out: Snapshot = {};
      for (const k of ["brand", "color", "condition"] as AttrKey[]) {
        const v = data.beforeParsed[k];
        if (v && String(v).trim() !== "") {
          out[k] = {
            value: String(v),
            confidence: null,
            confidence_version: "v3_raw_input",
            source: "manual",
          };
        }
      }
      return Object.keys(out).length ? out : null;
    }

    return null;
  }, [data?.before, data?.beforeParsed]);

  const after = useMemo(
    () =>
      normalizeSnapshot(data?.attributes, data?.tokens, data?.confidence_map),
    [data?.attributes, data?.tokens, data?.confidence_map]
  );

  // 初回に analyzer(after) を編集フォームへ流し込み（安全に一度だけ）
  const initialEdit = useMemo(() => {
    const base: Snapshot = {};

    for (const a of ATTRS) {
      // ① 人入力（beforeParsed）
      const raw = (data as any)?.beforeParsed?.[a.key] ?? null;

      if (raw && String(raw).trim() !== "") {
        base[a.key] = {
          value: String(raw),
          confidence: null,
          confidence_version: "v3_raw_input",
          source: "manual",
        };
        continue;
      }

      // ② fallback：解析結果
      const ai = after?.[a.key] ?? null;
      if (ai?.value) {
        base[a.key] = { ...ai };
      }
    }

    return base;
  }, [data?.beforeParsed, after]);

  // approve/reject では edit を持たない
  // edit_confirm/manual_override では after(AI) を初期値としてミラーする（beforeParsedは禁止）
  useEffect(() => {
    if (!after) return;

    if (mode === "manual_override") {
      setEdit(snapshotFromAI(after));
      // manual_override は自由入力なので selectedIds は使わない
      setSelectedIds({
        brand_entity_id: null,
        condition_entity_id: null,
        color_entity_id: null,
      });
      return;
    }

    if (mode === "edit_confirm") {
      setEdit(snapshotFromAI(after));
      // options が来るまで id は確定できないので、ここでは何もしない
      return;
    }

    // approve/reject
    setEdit({});
    setSelectedIds({
      brand_entity_id: null,
      condition_entity_id: null,
      color_entity_id: null,
    });
  }, [mode, after]);

  // edit_confirm 初期選択：after(AI) の canonical_name に一致する option があれば自動選択
  useEffect(() => {
    if (mode !== "edit_confirm") return;
    if (!after) return;
    if (
      !brandOptions.length &&
      !conditionOptions.length &&
      !colorOptions.length
    )
      return;

    const b = after.brand?.value ?? null;
    const c = after.condition?.value ?? null;
    const co = after.color?.value ?? null;

    const nextSelected: ResolvedEntitiesForBackend = {
      brand_entity_id: findIdByCanonicalName(brandOptions, b),
      condition_entity_id: findIdByCanonicalName(conditionOptions, c),
      color_entity_id: findIdByCanonicalName(colorOptions, co),
    };

    setSelectedIds(nextSelected);

    // 表示用ミラーも after(AI) に統一（念のため）
    setEdit(snapshotFromAI(after));
  }, [mode, after, brandOptions, conditionOptions, colorOptions]);

  const rows = useMemo(() => {
    return ATTRS.map((a) => {
      const b = before?.[a.key] ?? null;
      const ai = after?.[a.key] ?? null;
      const e = edit?.[a.key] ?? null;

      const state = diffState(b, ai);
      const badge = labelForDiff(state);

      const shownAfter =
        mode === "edit_confirm" || mode === "manual_override" ? e : ai;

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
    if (!(mode === "edit_confirm" || mode === "manual_override")) return false;
    if (maxConfidence === null) return false;
    return maxConfidence >= 0.7;
  }, [mode, maxConfidence]);

  const beforeParsedPayload = useMemo(
    () => ({
      brand: before?.brand?.value ?? null,
      color: before?.color?.value ?? null,
      condition: before?.condition?.value ?? null,
    }),
    [before]
  );

  const afterParsedPayload = useMemo(
    () => ({
      brand: after?.brand?.value ?? null,
      color: after?.color?.value ?? null,
      condition: after?.condition?.value ?? null,
    }),
    [after]
  );

  async function resolveBeforeDecide(): Promise<ResolvedEntitiesForBackend> {
    const resolved = await resolve({
      brand: after?.brand?.value ?? null,
      condition: after?.condition?.value ?? null,
      color: after?.color?.value ?? null,
    });

    return {
      brand_entity_id: resolved.brand_entity_id ?? null,
      condition_entity_id: resolved.condition_entity_id ?? null,
      color_entity_id: resolved.color_entity_id ?? null,
    };
  }

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
        // ★ ここが重要
        let msg = "Decide failed";
        try {
          const json = await res.json();
          msg = json?.message ?? msg;
        } catch {
          msg = await res.text();
        }
        throw new Error(msg);
      }

      await res.json().catch(() => ({}));
      await mutate(ENDPOINT.review);
      router.push(ENDPOINT.back);
    } finally {
      setIsSubmitting(false);
    }
  }

  /* ================= Render ================= */

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

          {/* ================= 🆕 Learning Writing ================= */}
          {data.beforeParsed && (
            <div className="border rounded-lg p-4 bg-green-50 space-y-2">
              <div className="text-sm font-semibold">
                フィールドを広げ解析エリア拡張計画
              </div>

              {data.beforeParsed.name && (
                <div className="text-sm">
                  <span className="font-medium">商品名：</span>
                  {data.beforeParsed.name}
                </div>
              )}

              {data.beforeParsed.description && (
                <div className="text-sm">
                  <span className="font-medium">商品説明：</span>
                  {data.beforeParsed.description}
                </div>
              )}
            </div>
          )}

          {/* ================= 🆕 Input Snapshot ================= */}
          {data.input_snapshot && (
            <details className="border rounded-lg p-4 bg-gray-50">
              <summary className="cursor-pointer text-sm font-semibold">
                Input Snapshot（全体入力データ）
              </summary>
              <pre className="mt-3 text-xs overflow-x-auto">
                {JSON.stringify(data.input_snapshot, null, 2)}
              </pre>
            </details>
          )}

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
          ← 解析一覧へ戻る
        </button>
      </div>

      {/* Mode switch */}
      <div className="border rounded-lg p-4 space-y-3">
        <div className="text-sm font-semibold">操作モード</div>
        <div className="flex flex-wrap gap-2">
          <ModePill
            label="Approve（解析結果採用）"
            active={mode === "approve"}
            onClick={() => setMode("approve")}
          />
          <ModePill
            label="Edit Confirm（選択して採用）"
            active={mode === "edit_confirm"}
            onClick={() => setMode("edit_confirm")}
          />
          <ModePill
            label="Manual Override（新規追加）"
            active={mode === "manual_override"}
            onClick={() => setMode("manual_override")}
          />
          <ModePill
            label="Reject（解析結果棄却：解析前入力採用（新規作成なし））"
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
              {mode === "edit_confirm" ? (
                (() => {
                  const key = r.key as AttrKey;

                  const options =
                    key === "brand"
                      ? brandOptions
                      : key === "condition"
                        ? conditionOptions
                        : colorOptions;

                  const selectedId =
                    key === "brand"
                      ? selectedIds.brand_entity_id
                      : key === "condition"
                        ? selectedIds.condition_entity_id
                        : selectedIds.color_entity_id;

                  return (
                    <select
                      className="w-full border rounded px-3 py-2 text-sm"
                      value={selectedId ?? ""}
                      onChange={(e) => {
                        const id = e.target.value
                          ? Number(e.target.value)
                          : null;

                        setSelectedIds((prev) => {
                          if (key === "brand")
                            return { ...prev, brand_entity_id: id };
                          if (key === "condition")
                            return { ...prev, condition_entity_id: id };
                          return { ...prev, color_entity_id: id };
                        });

                        // 表示用ミラー（after_snapshot.value のために canonical_name を同期）
                        const opt = options.find((o) => o.id === id);

                        setEdit((prev) => ({
                          ...prev,
                          [key]: {
                            ...(prev[key] ?? {}),
                            value: opt?.canonical_name ?? "",
                            source: "manual",
                            confidence_version: "v3_edit_confirm",
                            confidence: null,
                          },
                        }));
                      }}
                    >
                      <option value="">未選択</option>
                      {options.map((o) => (
                        <option key={o.id} value={o.id}>
                          {o.canonical_name}
                        </option>
                      ))}
                    </select>
                  );
                })()
              ) : mode === "manual_override" ? (
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
                          confidence: prev[r.key]?.confidence ?? null,
                          confidence_version:
                            prev[r.key]?.confidence_version ?? "v3_manual",
                          source: "manual",
                        },
                      }));
                    }}
                  />
                  <div className="text-xs text-gray-500">
                    解析（canonical）:{" "}
                    <span className="font-medium">{r.ai?.value ?? "-"}</span>
                    <span className="mx-2">·</span>
                    raw token:{" "}
                    <span className="font-medium">
                      {rawTokenFor(r.key as AttrKey, data.tokens) ?? "-"}
                    </span>
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
                if (mode === "reject") {
                  await submitDecision({
                    decision_type: "reject",
                    note: note || null,
                    beforeParsed: beforeParsedPayload,
                    afterParsed: afterParsedPayload,
                  });
                  return;
                }

                if (needsCautionPopup) {
                  const ok = confirm(
                    "confidence 70%以上の手動反映です。業務改善のため管理者へ通知されます。続行しますか？",
                  );
                  if (!ok) return;
                }

                let resolvedForBackend: ResolvedEntitiesForBackend | undefined =
                  undefined;

                if (mode === "approve") {
                  resolvedForBackend = await resolveBeforeDecide();
                  // approve は resolvedEntities 必須（固定）
                  if (
                    !resolvedForBackend.brand_entity_id &&
                    !resolvedForBackend.condition_entity_id &&
                    !resolvedForBackend.color_entity_id
                  ) {
                    alert(
                      "確定できるエンティティがありません。入力を確認してください。",
                    );
                    return;
                  }

                  await submitDecision({
                    decision_type: "approve",
                    resolvedEntities: resolvedForBackend,
                    // approve は after_snapshot を送らない（固定）
                    beforeParsed: beforeParsedPayload,
                    afterParsed: afterParsedPayload,
                    note: note || null,
                  });
                  return;
                }

                if (mode === "edit_confirm") {
                  // edit_confirm は「選択された entity_id」をそのまま backend に送る
                  resolvedForBackend = selectedIds;

                  if (
                    !resolvedForBackend.brand_entity_id &&
                    !resolvedForBackend.condition_entity_id &&
                    !resolvedForBackend.color_entity_id
                  ) {
                    alert("Edit Confirm は最低1つは選択してください。");
                    return;
                  }

                  await submitDecision({
                    decision_type: "edit_confirm",
                    resolvedEntities: resolvedForBackend,
                    // 表示・監査用に canonical_name を after_snapshot に同期（固定）
                    after_snapshot: buildAfterSnapshot(edit),
                    beforeParsed: beforeParsedPayload,
                    afterParsed: afterParsedPayload,
                    note: note || null,
                  });
                  return;
                }

                if (mode === "manual_override") {
                  // manual_override は自由入力 → resolvedEntities は null 固定
                  await submitDecision({
                    decision_type: "manual_override",
                    resolvedEntities: {
                      brand_entity_id: null,
                      condition_entity_id: null,
                      color_entity_id: null,
                    },
                    after_snapshot: buildAfterSnapshot(edit),
                    beforeParsed: beforeParsedPayload,
                    afterParsed: afterParsedPayload,
                    note: note || null,
                  });
                  return;
                }

                throw new Error("未対応の mode です");
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
