export type EntityType = "brand" | "condition" | "color";

export type Candidate = {
  value: string;
  score: number;
};

export type PolicyTrace = {
  threshold?: number;
  top_score?: number;
  accept_th?: number;
  review_th?: number;
  detail?: string;
  [k: string]: unknown;
};

export type KnowledgeSources = {
  policy?: {
    basePath?: string;
    entityPath?: string;
    version?: string;
  };
  alias?: {
    hit?: boolean;
    rawNorm?: string;
    aliasedTo?: string | null;
    aliasFile?: string;
  };
  audit?: {
    event?: string;
    ts?: string;
    path?: string;
  };
};

export type ReviewItem = {
  id: string;
  entity_type: EntityType;
  raw_value: string;

  decision: "auto_accept" | "human_review" | "rejected";
  confidence: number;
  rule_id: string;

  canonical_value?: string | null;
  candidates: Candidate[];

  created_at: string; // ISO
  updated_at?: string; // ISO

  // OmniCommerce Core 側の参照を将来入れられるように
  refs?: {
    item_id?: string | number;
    shop_id?: string | number;
    tenant_id?: string | number;
  };

  knowledge_sources?: KnowledgeSources;
  policy_trace?: PolicyTrace;
};

export type ReviewListResponse = {
  items: ReviewItem[];
  next_cursor?: string | null;
};

export type ResolveRequest = {
  action: "accept" | "reject" | "override";
  canonical_value?: string;
  note?: string;
};

export type ResolveResponse = {
  ok: true;
  item: ReviewItem;
};
