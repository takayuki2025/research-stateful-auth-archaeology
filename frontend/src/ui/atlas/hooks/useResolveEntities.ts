import type { ResolvePayload, ResolveResult } from "../types";

export function useResolveEntities(shopCode: string, requestId: string) {
  const resolve = async (payload: ResolvePayload): Promise<ResolveResult> => {
    const res = await fetch(
      `/api/shops/${shopCode}/atlas/requests/${requestId}/resolve`,
      {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      }
    );

    if (!res.ok) {
      const txt = await res.text().catch(() => "");
      throw new Error(txt || "Resolve failed");
    }

    return (await res.json()) as ResolveResult;
  };

  return { resolve };
}
