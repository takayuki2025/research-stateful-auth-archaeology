"use client";

import useSWR from "swr";
import type { ReviewListResponse } from "@/types/atlaskernel";
import { fetchReviewQueue } from "@/services/atlaskernelClient";

export function useReviewQueueSWR(params?: {
  status?: "pending" | "human_review" | "all";
  limit?: number;
  cursor?: string;
}) {
  const key = [
    "atlaskernel.reviewQueue",
    params?.status ?? "human_review",
    params?.limit ?? 50,
    params?.cursor ?? "",
  ];
  return useSWR<ReviewListResponse>(key, () => fetchReviewQueue(params), {
    revalidateOnFocus: true,
  });
}
