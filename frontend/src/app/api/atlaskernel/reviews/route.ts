import { NextResponse } from "next/server";
import type { ReviewListResponse } from "@/types/atlaskernel";

function mock(): ReviewListResponse {
  return {
    items: [
      {
        id: "rvw_mock_1",
        entity_type: "brand",
        raw_value: "アップル",
        decision: "human_review",
        confidence: 1.0,
        rule_id: "tie_break_human_review",
        canonical_value: null,
        candidates: [
          { value: "Apple", score: 1.0 },
          { value: "あっぷる", score: 1.0 },
        ],
        created_at: new Date().toISOString(),
        knowledge_sources: {
          policy: {
            basePath: "src/atlaskernel/policies/v2/_base.yaml",
            entityPath: "src/atlaskernel/policies/v2/brand.yaml",
            version: "v2",
          },
          alias: {
            hit: true,
            rawNorm: "あっぷる",
            aliasedTo: "Apple",
            aliasFile: "src/atlaskernel/assets/brand_alias.txt",
          },
          audit: {
            event: "policy_decision",
            ts: new Date().toISOString(),
            path: "var/log/atlaskernel_audit.jsonl",
          },
        },
        policy_trace: { top_score: 1.0, detail: "top scores tied" },
      },
    ],
    next_cursor: null,
  };
}

export async function GET(req: Request) {
  const useMock = process.env.ATLAS_USE_MOCK === "1";
  if (useMock) return NextResponse.json(mock());

  const url = new URL(req.url);
  const status = url.searchParams.get("status") ?? "human_review";
  const limit = url.searchParams.get("limit") ?? "50";
  const cursor = url.searchParams.get("cursor");

  const base = process.env.ATLAS_INTERNAL_URL;
  if (!base)
    return NextResponse.json(
      { error: "ATLAS_INTERNAL_URL is not set" },
      { status: 500 }
    );

  const upstream = new URL("/v1/reviews", base);
  upstream.searchParams.set("status", status);
  upstream.searchParams.set("limit", limit);
  if (cursor) upstream.searchParams.set("cursor", cursor);

  const res = await fetch(upstream.toString(), {
    method: "GET",
    headers: { Accept: "application/json" },
    cache: "no-store",
  });

  const text = await res.text();
  return new NextResponse(text, {
    status: res.status,
    headers: {
      "Content-Type": res.headers.get("content-type") ?? "application/json",
    },
  });
}
