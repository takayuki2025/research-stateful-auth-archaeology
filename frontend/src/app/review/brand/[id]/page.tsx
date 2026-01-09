"use client";

import { useEffect, useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";

type Candidate = { value: string; score: number };
type ReviewDetail = {
  id: string;
  entity_type: "brand";
  raw_value: string;
  result: {
    canonical_value: string | null;
    confidence: number;
    decision: string;
    rule_id: string;
    candidates: Candidate[];
    explanation: any[];
    extensions: any;
  };
  evidence?: {
    image_path?: string | null;
    detected_text?: string[];
    brand_conflict?: boolean;
    image_confidence?: number;
  } | null;
  created_at: string;
};

export default function BrandReviewDetailPage() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();

  const [detail, setDetail] = useState<ReviewDetail | null>(null);
  const [decisionValue, setDecisionValue] = useState<string>("");
  const [note, setNote] = useState("");

  useEffect(() => {
    const run = async () => {
      const res = await fetch(`/api/review/brand/${id}`, { cache: "no-store" });
      const data = await res.json();
      setDetail(data);
      setDecisionValue(data?.result?.canonical_value ?? "");
    };
    run();
  }, [id]);

  const topCandidates = useMemo(
    () => detail?.result?.candidates ?? [],
    [detail]
  );

  const submit = async (action: "commit" | "reject") => {
    const res = await fetch(`/api/review/brand/${id}/decision`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        decision: action,
        canonical_value: action === "commit" ? decisionValue : null,
        note,
      }),
    });

    if (!res.ok) {
      alert("Failed to submit decision");
      return;
    }
    router.push("/review/brand?status=pending");
  };

  if (!detail) return <div style={{ padding: 24 }}>Loading...</div>;

  return (
    <div
      style={{
        padding: 24,
        display: "grid",
        gridTemplateColumns: "1.2fr 0.8fr",
        gap: 16,
      }}
    >
      <div>
        <h1 style={{ fontSize: 20, fontWeight: 700 }}>
          Brand Review #{detail.id}
        </h1>
        <div style={{ marginTop: 8 }}>
          <div>
            <b>Raw:</b> {detail.raw_value}
          </div>
          <div>
            <b>Policy:</b> {detail.result.rule_id} / conf=
            {detail.result.confidence.toFixed(3)}
          </div>
          <div>
            <b>Decision (engine):</b> {detail.result.decision}
          </div>
        </div>

        <h2 style={{ marginTop: 16, fontSize: 16, fontWeight: 700 }}>
          Candidates
        </h2>
        <ul>
          {topCandidates.map((c) => (
            <li key={c.value}>
              {c.value} — {c.score.toFixed(3)}
            </li>
          ))}
        </ul>

        <h2 style={{ marginTop: 16, fontSize: 16, fontWeight: 700 }}>
          Policy Trace
        </h2>
        <pre style={{ background: "#f7f7f7", padding: 12, overflow: "auto" }}>
          {JSON.stringify(
            detail.result.extensions?.policy_trace ?? {},
            null,
            2
          )}
        </pre>

        <h2 style={{ marginTop: 16, fontSize: 16, fontWeight: 700 }}>
          Explanation
        </h2>
        <pre style={{ background: "#f7f7f7", padding: 12, overflow: "auto" }}>
          {JSON.stringify(detail.result.explanation ?? [], null, 2)}
        </pre>
      </div>

      <div>
        <h2 style={{ fontSize: 16, fontWeight: 700 }}>Evidence</h2>
        <pre style={{ background: "#f7f7f7", padding: 12, overflow: "auto" }}>
          {JSON.stringify(detail.evidence ?? {}, null, 2)}
        </pre>

        <h2 style={{ marginTop: 16, fontSize: 16, fontWeight: 700 }}>
          Human Decision
        </h2>
        <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
          <input
            value={decisionValue}
            onChange={(e) => setDecisionValue(e.target.value)}
            placeholder="canonical_value"
            style={{ padding: 10 }}
          />
          <textarea
            value={note}
            onChange={(e) => setNote(e.target.value)}
            placeholder="note (optional)"
            style={{ padding: 10, minHeight: 90 }}
          />
          <button onClick={() => submit("commit")} style={{ padding: 10 }}>
            Commit
          </button>
          <button onClick={() => submit("reject")} style={{ padding: 10 }}>
            Reject
          </button>
        </div>
      </div>
    </div>
  );
}
