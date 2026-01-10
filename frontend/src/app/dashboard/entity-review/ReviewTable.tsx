"use client";

import React from "react";
import type { Candidate } from "./types";

function fmt(score: number) {
  return score.toFixed(2);
}

export function ReviewTable(props: {
  candidates: Candidate[];
  selected: string | null;
  onSelect: (v: string) => void;
}) {
  const { candidates, selected, onSelect } = props;

  // 同点検出（上位2つが同点なら警告）
  const tieTop =
    candidates.length >= 2 && candidates[0].score === candidates[1].score;

  return (
    <div className="rounded-2xl border p-4">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-semibold">Candidates</h3>
        {tieTop ? (
          <span className="text-sm font-medium text-orange-600">
            Tie detected at top score
          </span>
        ) : null}
      </div>

      <div className="mt-3 space-y-2">
        {candidates.map((c) => (
          <label
            key={c.value}
            className={`flex cursor-pointer items-center justify-between rounded-xl border px-3 py-2 ${
              selected === c.value ? "ring-2" : ""
            }`}
          >
            <div className="flex items-center gap-3">
              <input
                type="radio"
                name="candidate"
                checked={selected === c.value}
                onChange={() => onSelect(c.value)}
              />
              <div className="text-base">{c.value}</div>
            </div>
            <div className="text-sm tabular-nums">{fmt(c.score)}</div>
          </label>
        ))}
      </div>
    </div>
  );
}
