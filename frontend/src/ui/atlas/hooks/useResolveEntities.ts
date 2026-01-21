import type { ResolvePayload, ResolveResult } from "../types";
import { useAuth } from "@/ui/auth/AuthProvider";

function normalizeApiPath(path: string): string {
  return path.startsWith("/api/") ? path.replace(/^\/api/, "") : path;
}

export function useResolveEntities(shopCode: string, requestId: string) {
  const { apiClient } = useAuth() as any;

  const resolve = async (payload: ResolvePayload): Promise<ResolveResult> => {
    const r = await apiClient.post(
      normalizeApiPath(
        `/api/shops/${shopCode}/atlas/requests/${requestId}/resolve`,
      ),
      payload,
    );

    const data =
      r && typeof r === "object" && "data" in r ? (r as any).data : r;
    return data as ResolveResult;
  };

  return { resolve };
}
