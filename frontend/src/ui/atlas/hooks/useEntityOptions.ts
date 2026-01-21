import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";

export type EntityOption = {
  id: number;
  canonical_name: string;
};

function normalizeApiPath(path: string): string {
  // apiClient が /api prefix を付ける前提なので /api を剥がして統一
  return path.startsWith("/api/") ? path.replace(/^\/api/, "") : path;
}

export function useEntityOptions(
  kind: "brands" | "conditions" | "colors",
  enabled: boolean,
) {
  const { apiClient } = useAuth() as any;

  const url = enabled ? `/entities/${kind}` : null;

  const fetcher = async (u: string): Promise<EntityOption[]> => {
    const r = await apiClient.get(normalizeApiPath(u));
    const data =
      r && typeof r === "object" && "data" in r ? (r as any).data : r;
    return data as EntityOption[];
  };

  return useSWR<EntityOption[]>(url, fetcher);
}
