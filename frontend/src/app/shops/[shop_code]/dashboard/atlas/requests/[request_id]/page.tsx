"use client";

import { useEffect, useState, useCallback } from "react";
import { useAuth } from "@/ui/auth/AuthProvider";

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

  const { apiClient } = useAuth() as any;

  const [request, setRequest] = useState<AnalysisRequest | null>(null);
  const [result, setResult] = useState<AnalysisResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // axios-like ({data}) / fetch-like (plain) 両対応
  const unwrap = <T,>(r: any): T => {
    if (r && typeof r === "object" && "data" in r) return r.data as T;
    return r as T;
  };

  const load = useCallback(async () => {
    try {
      setError(null);

      // "/api" は apiClient 側で付く前提なので外す
      const r = await apiClient.get(`/atlas/requests/${requestId}`);
      const data = unwrap<{ request: AnalysisRequest; result: AnalysisResult }>(
        r,
      );

      setRequest(data.request);
      setResult(data.result);
    } catch (e: any) {
      setError(e?.message ?? "Failed to load request");
    }
  }, [apiClient, requestId]);

  useEffect(() => {
    load();
  }, [load]);

  async function replay() {
    setLoading(true);
    try {
      setError(null);
      // "/api" を外す + apiClient に統一（JWT/IdaaSでも動く）
      await apiClient.post(`/atlas/requests/${requestId}/replay`, {});
      // 再取得（元の「再取得」挙動を維持）
      await load();
    } catch (e: any) {
      setError(e?.message ?? "Replay failed");
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
