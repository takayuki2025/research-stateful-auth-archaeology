"use client";

import React, { useMemo, useState } from "react";
import type { AnalyzeResponse } from "./types";
import { ReviewTable } from "./ReviewTable";
import { DecisionPanel } from "./DecisionPanel";

export default function EntityReviewPage() {
  const [entityType, setEntityType] = useState<"brand" | "condition" | "color">(
    "brand"
  );
  const [rawValue, setRawValue] = useState("アップル");
  const [analysis, setAnalysis] = useState<AnalyzeResponse | null>(null);
  const [selected, setSelected] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const tieTop = useMemo(() => {
    if (!analysis) return false;
    return (
      analysis.candidates.length >= 2 &&
      analysis.candidates[0].score === analysis.candidates[1].score
    );
  }, [analysis]);

  async function runAnalyze() {
    setError(null);
    setLoading(true);
    setSelected(null);

    try {
      const res = await fetch("/api/atlas/analyze", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          entity_type: entityType,
          raw_value: rawValue,
          known_assets_ref:
            entityType === "brand"
              ? "brands_v1"
              : entityType === "condition"
                ? "conditions_v1"
                : "colors_v1",
          context: {
            categories: ["家電", "本"],
          },
        }),
      });

      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data?.message ?? "Analyze failed");
      }

      const data = (await res.json()) as AnalyzeResponse;
      setAnalysis(data);

      // UX: auto_accept なら canonical をデフォルト選択
      if (data.canonical_value) setSelected(data.canonical_value);
    } catch (e: any) {
      setError(e?.message ?? "Unknown error");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="mx-auto max-w-5xl p-6">
      <h1 className="text-2xl font-semibold">Entity Review</h1>

      <div className="mt-4 rounded-2xl border p-4">
        <div className="grid gap-3 md:grid-cols-3">
          <div>
            <label className="text-sm text-gray-600">Entity Type</label>
            <select
              className="mt-1 w-full rounded-xl border p-2"
              value={entityType}
              onChange={(e) =>
                setEntityType(e.target.value as "brand" | "condition" | "color")
              }
            >
              <option value="brand">brand</option>
              <option value="condition">condition</option>
              <option value="color">color</option>
            </select>
          </div>

          <div className="md:col-span-2">
            <label className="text-sm text-gray-600">Raw Value</label>
            <input
              className="mt-1 w-full rounded-xl border p-2"
              value={rawValue}
              onChange={(e) => setRawValue(e.target.value)}
              placeholder="例: アップル / 美品 / 青"
            />
          </div>
        </div>

        <div className="mt-4 flex items-center gap-3">
          <button
            className="rounded-xl border px-4 py-2 text-sm font-semibold"
            onClick={runAnalyze}
            disabled={loading}
          >
            {loading ? "Analyzing..." : "Analyze"}
          </button>

          {analysis ? (
            <span className="text-sm text-gray-600">
              decision: <span className="font-mono">{analysis.decision}</span>{" "}
              confidence:{" "}
              <span className="font-mono">
                {analysis.confidence.toFixed(2)}
              </span>
              {tieTop ? (
                <span className="ml-2 font-medium text-orange-600">
                  (top tie)
                </span>
              ) : null}
            </span>
          ) : null}
        </div>

        {error ? (
          <p className="mt-3 text-sm font-medium text-red-600">{error}</p>
        ) : null}
      </div>

      {analysis ? (
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          <ReviewTable
            candidates={analysis.candidates}
            selected={selected}
            onSelect={setSelected}
          />

          <div className="space-y-4">
            <DecisionPanel analysis={analysis} selected={selected} />

            <div className="rounded-2xl border p-4">
              <h3 className="text-lg font-semibold">Policy Trace</h3>
              <pre className="mt-3 overflow-auto rounded-xl border p-3 text-xs">
                {JSON.stringify(
                  {
                    explanation: analysis.explanation,
                    extensions: analysis.extensions,
                  },
                  null,
                  2
                )}
              </pre>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  );
}
