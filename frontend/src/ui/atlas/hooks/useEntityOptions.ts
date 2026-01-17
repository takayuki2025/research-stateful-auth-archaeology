import useSWR from "swr";

export type EntityOption = {
  id: number;
  canonical_name: string;
};

const fetcher = async (u: string): Promise<EntityOption[]> => {
  const res = await fetch(u, { credentials: "include" });
  if (!res.ok) {
    const txt = await res.text().catch(() => "");
    throw new Error(txt || "Fetch failed");
  }
  return (await res.json()) as EntityOption[];
};

/**
 * useEntityOptions
 * - Edit Confirm 時のみ canonical entity 候補を取得する
 */
export function useEntityOptions(
  kind: "brands" | "conditions" | "colors",
  enabled: boolean
) {
  const url = enabled ? `/api/entities/${kind}` : null;
  return useSWR<EntityOption[]>(url, fetcher);
}
