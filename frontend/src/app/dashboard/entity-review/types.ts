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
