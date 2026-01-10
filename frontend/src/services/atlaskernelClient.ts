import type {
  ReviewListResponse,
  ResolveRequest,
  ResolveResponse,
} from "@/types/atlaskernel";

async function jsonOrThrow(res: Response) {
  if (!res.ok) {
    const text = await res.text().catch(() => "");
    throw new Error(`Request failed: ${res.status} ${res.statusText} ${text}`);
  }
  return res.json();
}

export async function fetchReviewQueue(params?: {
  status?: "pending" | "human_review" | "all";
  limit?: number;
  cursor?: string;
}): Promise<ReviewListResponse> {
  const sp = new URLSearchParams();
  sp.set("status", params?.status ?? "human_review");
  sp.set("limit", String(params?.limit ?? 50));
  if (params?.cursor) sp.set("cursor", params.cursor);

  const res = await fetch(`/api/atlaskernel/reviews?${sp.toString()}`, {
    method: "GET",
    headers: { Accept: "application/json" },
    cache: "no-store",
  });
  return jsonOrThrow(res);
}

export async function resolveReview(
  id: string,
  body: ResolveRequest
): Promise<ResolveResponse> {
  const res = await fetch(
    `/api/atlaskernel/reviews/${encodeURIComponent(id)}/resolve`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    }
  );
  return jsonOrThrow(res);
}
