export type AnalyzeRequest = {
  entity_type: "brand" | "condition" | "color";
  raw_value: string;
  known_assets_ref?: string; // 例: "brands_v1"
  context?: {
    categories?: string[];
    tenant_id?: string | number | null;
    shop_id?: string | number | null;
  };
};

export type Candidate = { value: string; score: number };

export type AnalyzeResponse = {
  entity_type: string;
  raw_value: string;
  canonical_value: string | null;
  confidence: number;
  decision: "auto_accept" | "human_review" | "rejected";
  rule_id: string;
  candidates: Candidate[];
  explanation?: Array<{
    rule: string;
    confidence: number;
    trace?: Record<string, unknown>;
  }>;
  extensions?: Record<string, unknown>;
};

const ATLAS_BASE_URL =
  process.env.ATLAS_BASE_URL ?? "http://python_atlaskernel:8000";

export async function atlasAnalyze(
  req: AnalyzeRequest
): Promise<AnalyzeResponse> {
  const res = await fetch(`${ATLAS_BASE_URL}/analyze`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(req),
    // Node runtime で docker 内通信する想定
    cache: "no-store",
  });

  if (!res.ok) {
    const text = await res.text().catch(() => "");
    throw new Error(`Atlas analyze failed: ${res.status} ${text}`);
  }

  return res.json();
}
