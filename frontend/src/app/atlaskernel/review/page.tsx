"use client";

import { useMemo, useState } from "react";
import { useReviewQueueSWR } from "@/services/useReviewQueueSWR";
import { resolveReview } from "@/services/atlaskernelClient";
import type { ReviewItem } from "@/types/atlaskernel";

function fmt(n: number) {
  return (Math.round(n * 1000) / 1000).toFixed(3);
}

function Badge({ children }: { children: React.ReactNode }) {
  return (
    <span className="inline-flex items-center rounded-full border px-2 py-0.5 text-xs">
      {children}
    </span>
  );
}

function DecisionBadge({ d }: { d: ReviewItem["decision"] }) {
  const label =
    d === "human_review"
      ? "Human Review"
      : d === "auto_accept"
        ? "Auto Accept"
        : "Rejected";
  return <Badge>{label}</Badge>;
}

function PanelTitle({ children }: { children: React.ReactNode }) {
  return (
    <div className="text-sm font-semibold tracking-wide text-gray-700">
      {children}
    </div>
  );
}

export default function AtlasKernelReviewPage() {
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const { data, error, isLoading, mutate } = useReviewQueueSWR({
    status: "human_review",
    limit: 50,
  });

  const selected = useMemo(() => {
    if (!data?.items?.length) return null;
    const found = selectedId
      ? data.items.find((x) => x.id === selectedId)
      : null;
    return found ?? data.items[0];
  }, [data, selectedId]);

  async function onResolve(action: "accept" | "reject" | "override") {
    if (!selected) return;
    const canonical =
      action === "override"
        ? (prompt("canonical_value を入力してください（例: Apple）") ?? "")
        : undefined;

    await resolveReview(selected.id, {
      action,
      canonical_value:
        canonical && canonical.length > 0 ? canonical : undefined,
      note: "resolved by review UI",
    });

    await mutate(); // 取り直し
  }

  return (
    <div className="min-h-[calc(100vh-2rem)] p-4">
      <div className="mb-3">
        <h1 className="text-xl font-semibold">AtlasKernel Review Queue</h1>
        <p className="text-sm text-gray-600">
          同点（tie）や閾値境界の案件を安全に “human_review” として処理します。
        </p>
      </div>

      {error && (
        <div className="rounded-lg border p-3 text-sm text-red-700">
          Error: {String(error?.message ?? error)}
        </div>
      )}

      <div className="grid grid-cols-12 gap-4">
        {/* Left: queue */}
        <div className="col-span-5 rounded-xl border bg-white">
          <div className="flex items-center justify-between border-b p-3">
            <PanelTitle>Queue</PanelTitle>
            <Badge>{data?.items?.length ?? 0} items</Badge>
          </div>

          {isLoading && (
            <div className="p-3 text-sm text-gray-600">Loading...</div>
          )}

          <div className="max-h-[70vh] overflow-auto">
            {(data?.items ?? []).map((it) => (
              <button
                key={it.id}
                className={`w-full border-b p-3 text-left hover:bg-gray-50 ${selected?.id === it.id ? "bg-gray-50" : ""}`}
                onClick={() => setSelectedId(it.id)}
              >
                <div className="flex items-center justify-between gap-2">
                  <div className="font-medium">{it.raw_value}</div>
                  <DecisionBadge d={it.decision} />
                </div>
                <div className="mt-1 flex items-center gap-2 text-xs text-gray-600">
                  <Badge>{it.entity_type}</Badge>
                  <span>confidence: {fmt(it.confidence)}</span>
                  <span className="truncate">rule: {it.rule_id}</span>
                </div>

                <div className="mt-2 text-xs text-gray-500">
                  Top candidates:{" "}
                  {(it.candidates ?? [])
                    .slice(0, 3)
                    .map((c) => `${c.value} (${fmt(c.score)})`)
                    .join(", ")}
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Right: detail */}
        <div className="col-span-7 rounded-xl border bg-white">
          <div className="flex items-center justify-between border-b p-3">
            <PanelTitle>Detail</PanelTitle>
            {selected && (
              <div className="flex gap-2">
                <button
                  className="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50"
                  onClick={() => onResolve("accept")}
                >
                  Accept
                </button>
                <button
                  className="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50"
                  onClick={() => onResolve("reject")}
                >
                  Reject
                </button>
                <button
                  className="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50"
                  onClick={() => onResolve("override")}
                >
                  Override
                </button>
              </div>
            )}
          </div>

          {!selected && (
            <div className="p-3 text-sm text-gray-600">No item selected.</div>
          )}

          {selected && (
            <div className="grid grid-cols-12 gap-4 p-3">
              {/* Main */}
              <div className="col-span-7">
                <div className="mb-2">
                  <div className="text-lg font-semibold">
                    {selected.raw_value}
                  </div>
                  <div className="mt-1 flex items-center gap-2 text-sm text-gray-700">
                    <Badge>{selected.entity_type}</Badge>
                    <DecisionBadge d={selected.decision} />
                    <span>confidence: {fmt(selected.confidence)}</span>
                  </div>
                  <div className="mt-1 text-xs text-gray-500">
                    rule_id:{" "}
                    <span className="font-mono">{selected.rule_id}</span>
                  </div>
                </div>

                <div className="rounded-lg border">
                  <div className="border-b p-2 text-sm font-semibold">
                    Candidates
                  </div>
                  <table className="w-full text-sm">
                    <thead className="text-left text-xs text-gray-600">
                      <tr>
                        <th className="p-2">value</th>
                        <th className="p-2">score</th>
                      </tr>
                    </thead>
                    <tbody>
                      {(selected.candidates ?? []).map((c, idx) => (
                        <tr key={`${c.value}-${idx}`} className="border-t">
                          <td className="p-2 font-medium">{c.value}</td>
                          <td className="p-2 font-mono">{fmt(c.score)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <div className="mt-3 rounded-lg border p-2">
                  <div className="text-sm font-semibold">Policy Trace</div>
                  <pre className="mt-2 overflow-auto rounded bg-gray-50 p-2 text-xs">
                    {JSON.stringify(selected.policy_trace ?? {}, null, 2)}
                  </pre>
                </div>
              </div>

              {/* Sources */}
              <div className="col-span-5">
                <div className="rounded-lg border p-2">
                  <div className="text-sm font-semibold">Knowledge Sources</div>

                  <div className="mt-2 space-y-3 text-xs">
                    <div>
                      <div className="font-semibold">policy</div>
                      <pre className="mt-1 rounded bg-gray-50 p-2">
                        {JSON.stringify(
                          selected.knowledge_sources?.policy ?? {},
                          null,
                          2
                        )}
                      </pre>
                    </div>

                    <div>
                      <div className="font-semibold">alias</div>
                      <pre className="mt-1 rounded bg-gray-50 p-2">
                        {JSON.stringify(
                          selected.knowledge_sources?.alias ?? {},
                          null,
                          2
                        )}
                      </pre>
                    </div>

                    <div>
                      <div className="font-semibold">audit/log</div>
                      <pre className="mt-1 rounded bg-gray-50 p-2">
                        {JSON.stringify(
                          selected.knowledge_sources?.audit ?? {},
                          null,
                          2
                        )}
                      </pre>
                    </div>
                  </div>
                </div>

                <div className="mt-3 rounded-lg border p-2">
                  <div className="text-sm font-semibold">Operator Note</div>
                  <p className="mt-1 text-xs text-gray-600">
                    tie の場合は原則 human_review。Override で canonical_value
                    を明示すると、辞書/alias へ反映する導線にできます。
                  </p>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
