"use client";

import { useEffect, useState } from "react";

type AnalysisRequest = {
  id: number;
  item_id: number;
  analysis_version: string;
  status: "pending" | "running" | "done" | "failed";
};

type AnalysisResult = {
  payload: any;
};

export default function AnalysisRequestReviewPage({
  params,
}: {
  params: { request_id: string; shop_code: string };
}) {
  const requestId = params.request_id;

  const [request, setRequest] = useState<AnalysisRequest | null>(null);
  const [result, setResult] = useState<AnalysisResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function load() {
      try {
        const res = await fetch(`/api/atlas/requests/${requestId}`);
        if (!res.ok) throw new Error("Failed to load request");
        const data = await res.json();
        setRequest(data.request);
        setResult(data.result);
      } catch (e: any) {
        setError(e.message);
      }
    }
    load();
  }, [requestId]);

  async function replay() {
    setLoading(true);
    try {
      const res = await fetch(`/api/atlas/requests/${requestId}/replay`, {
        method: "POST",
      });
      if (!res.ok) throw new Error("Replay failed");
      // 再取得
      location.reload();
    } catch (e: any) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }

  if (error) return <p className="text-red-600">{error}</p>;
  if (!request || !result) return <p>Loading...</p>;

  return (
    <div className="mx-auto max-w-5xl p-6 space-y-6">
      <h1 className="text-2xl font-semibold">Analysis Request #{request.id}</h1>

      <section className="rounded-xl border p-4">
        <h2 className="font-semibold">Request</h2>
        <pre className="text-xs mt-2">{JSON.stringify(request, null, 2)}</pre>
      </section>

      <section className="rounded-xl border p-4">
        <h2 className="font-semibold">Analysis Result</h2>
        <pre className="text-xs mt-2 overflow-auto">
          {JSON.stringify(result.payload, null, 2)}
        </pre>
      </section>

      <div className="flex gap-2">
        <button
          className="rounded-xl border px-4 py-2 font-semibold"
          onClick={replay}
          disabled={loading || request.status !== "done"}
        >
          Replay
        </button>

        <button className="rounded-xl border px-4 py-2">
          Approve（次フェーズ）
        </button>

        <button className="rounded-xl border px-4 py-2">
          Reject（次フェーズ）
        </button>
      </div>
    </div>
  );
}
