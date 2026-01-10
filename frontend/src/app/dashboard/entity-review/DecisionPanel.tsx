"use client";

import React, { useMemo, useState } from "react";
import type { AnalyzeResponse } from "./types";

export function DecisionPanel(props: {
  analysis: AnalyzeResponse;
  selected: string | null;
}) {
  const { analysis, selected } = props;
  const [note, setNote] = useState("");

  const disabledAccept = analysis.decision === "rejected" && !selected;

  const payloadBase = useMemo(
    () => ({
      entity_type: analysis.entity_type,
      raw_value: analysis.raw_value,
      analysis,
    }),
    [analysis]
  );

  async function submit(action: "accept" | "reject" | "escalate") {
    const res = await fetch("/api/atlas/review", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        ...payloadBase,
        selected_value: selected,
        action,
        note: note || undefined,
      }),
    });

    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data?.message ?? "Submit failed");
    }
  }

  return (
    <div className="rounded-2xl border p-4">
      <h3 className="text-lg font-semibold">Decision</h3>

      <div className="mt-3 space-y-2 text-sm">
        <div className="flex justify-between">
          <span className="text-gray-600">rule_id</span>
          <span className="font-mono">{analysis.rule_id}</span>
        </div>
        <div className="flex justify-between">
          <span className="text-gray-600">decision</span>
          <span className="font-mono">{analysis.decision}</span>
        </div>
        <div className="flex justify-between">
          <span className="text-gray-600">confidence</span>
          <span className="font-mono">{analysis.confidence.toFixed(2)}</span>
        </div>
      </div>

      <div className="mt-4">
        <label className="text-sm text-gray-600">Note</label>
        <textarea
          className="mt-1 w-full rounded-xl border p-2 text-sm"
          rows={4}
          value={note}
          onChange={(e) => setNote(e.target.value)}
          placeholder="Optional reviewer note..."
        />
      </div>

      <div className="mt-4 flex gap-2">
        <button
          className="rounded-xl border px-4 py-2 text-sm font-semibold"
          onClick={() => submit("accept")}
          disabled={disabledAccept}
        >
          Accept
        </button>
        <button
          className="rounded-xl border px-4 py-2 text-sm font-semibold"
          onClick={() => submit("reject")}
        >
          Reject
        </button>
        <button
          className="rounded-xl border px-4 py-2 text-sm font-semibold"
          onClick={() => submit("escalate")}
        >
          Escalate
        </button>
      </div>

      <p className="mt-3 text-xs text-gray-500">
        Accept uses selected candidate when provided. Escalate is the safe
        option when top scores tie or context is ambiguous.
      </p>
    </div>
  );
}
