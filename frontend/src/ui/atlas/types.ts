export type AttrKey = "brand" | "color" | "condition";

/* ========= Resolve ========= */

export type ResolvePayload = {
  brand?: string | null;
  condition?: string | null;
  color?: string | null;
};

export type ResolveResult = {
  brand_entity_id?: number | null;
  condition_entity_id?: number | null;
  color_entity_id?: number | null;
};

/* ========= Atlas Analyze ========= */

export type AtlasTokens = {
  brand?: string[];
  condition?: string[];
  color?: string[];
};

export type ConfidenceMap = {
  brand?: number | null;
  condition?: number | null;
  color?: number | null;
};

export type AtlasAttribute = {
  value: string | null;
  confidence: number | null;
  source: "ai";
  evidence?: unknown;
};

export type AtlasAttributes = {
  brand?: AtlasAttribute;
  condition?: AtlasAttribute;
  color?: AtlasAttribute;
};

/* ========= Human ========= */

export type HumanAttribute = {
  value: string;
  confidence: number | null;
  source: "manual";
};

export type AfterSnapshot = {
  brand?: HumanAttribute;
  condition?: HumanAttribute;
  color?: HumanAttribute;
};

/* ========= UI ========= */

export type AttrValue = {
  value: string | null;
  confidence?: number | null;
  confidence_version?: string | null;
  source?: "ai" | "manual";
};

export type Snapshot = Partial<Record<AttrKey, AttrValue>>;
