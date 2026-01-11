"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { ConfidenceBar } from "./components/ConfidenceBar";
import { ConfidenceCircle } from "./components/ConfidenceCircle";

type Analysis = any;

export default function ReviewPage() {
  const params = useParams();
  const itemId = params.id as string;

  const [analysis, setAnalysis] = useState<Analysis | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch(`/api/items/${itemId}/analysis`)
      .then((res) => res.json())
      .then((data) => {
        setAnalysis(data.analysis ?? null);
        setLoading(false);
      });
  }, [itemId]);

  if (loading) return <div>Loading...</div>;
  if (!analysis) return <div>未解析</div>;

  const brand = analysis.integration?.brand_identity;

  return (
    <div style={{ padding: 24 }}>
      <h1>AI 解析レビュー</h1>

      {/* ブランド */}
      {brand && (
        <section style={{ marginTop: 24 }}>
          <h2>ブランド</h2>
          <div style={{ display: "flex", gap: 16, alignItems: "center" }}>
            <div>
              <strong>{brand.canonical}</strong>
              <ConfidenceBar value={brand.confidence} />
            </div>
            <ConfidenceCircle value={brand.confidence} />
          </div>
        </section>
      )}

      {/* 再解析 */}
      <button
        style={{ marginTop: 32 }}
        onClick={() =>
          fetch(`/api/items/${itemId}/analysis/reanalyze`, {
            method: "POST",
          }).then(() => alert("再解析しました"))
        }
      >
        再解析する（AI）
      </button>
    </div>
  );
}
