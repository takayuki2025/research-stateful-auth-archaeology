"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";

type AttributeDiff = {
  before: string | null;
  after: string | null;
  confidence?: number | null;
};

type ReviewResponse = {
  attributes: Record<string, AttributeDiff>;
  status: string;
};

export default function AtlasReviewPage() {
  const params = useParams();
  const router = useRouter();

  const shopCode = params.shopCode as string;
  const analysisRequestId = params.analysisRequestId as string;

  const [data, setData] = useState<ReviewResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [note, setNote] = useState("");

  useEffect(() => {
    fetch(`/api/shops/${shopCode}/atlas/reviews/${analysisRequestId}`)
      .then((res) => res.json())
      .then(setData)
      .finally(() => setLoading(false));
  }, [shopCode, analysisRequestId]);

  if (loading) return <div className="p-6">Loading...</div>;
  if (!data) return <div className="p-6">Not found</div>;

  const submit = async (decisionType: string) => {
    // confidence >= 0.7 の警告
    const risky = Object.values(data.attributes).some(
      (a) =>
        a.confidence !== null &&
        a.confidence !== undefined &&
        a.confidence >= 0.7
    );

    if (decisionType === "edit_confirm" && risky) {
      const ok = confirm(
        "高信頼（confidence ≥ 0.7）の解析結果を上書きします。\nこの操作は管理者に通知されます。"
      );
      if (!ok) return;
    }

    await fetch(
      `/api/shops/${shopCode}/atlas/reviews/${analysisRequestId}/decide`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          decisionType,
          afterSnapshot: decisionType === "approve" ? null : data.attributes,
          note,
        }),
      }
    );

    router.push(`/shops/${shopCode}/atlas`);
  };

  return (
    <div className="p-8 space-y-6">
      <h1 className="text-xl font-bold">Atlas Review #{analysisRequestId}</h1>

      <table className="w-full border">
        <thead>
          <tr className="bg-gray-100">
            <th className="p-2">Attribute</th>
            <th className="p-2">Before</th>
            <th className="p-2">After</th>
            <th className="p-2">Confidence</th>
          </tr>
        </thead>
        <tbody>
          {Object.entries(data.attributes).map(([key, attr]) => (
            <tr key={key} className="border-t">
              <td className="p-2 font-mono">{key}</td>
              <td className="p-2">{attr.before ?? "—"}</td>
              <td
                className={`p-2 ${attr.confidence && attr.confidence >= 0.7 ? "bg-red-50" : ""}`}
              >
                {attr.after ?? "—"}
              </td>
              <td className="p-2">
                {attr.confidence !== undefined && attr.confidence !== null
                  ? attr.confidence.toFixed(2)
                  : "—"}
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <textarea
        className="w-full border p-2"
        placeholder="Decision note (optional)"
        value={note}
        onChange={(e) => setNote(e.target.value)}
      />

      <div className="flex gap-4">
        <button className="btn" onClick={() => submit("approve")}>
          Approve
        </button>
        <button className="btn" onClick={() => submit("edit_confirm")}>
          Edit & Confirm
        </button>
        <button className="btn" onClick={() => submit("reject")}>
          Reject
        </button>
      </div>
    </div>
  );
}
