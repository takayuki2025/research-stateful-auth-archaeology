"use client";

import { useEffect } from "react";
import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
import type { Item, ItemComment } from "@/types/item";

export type ItemDetailResponse = {
  item: Item; // ★ ここを統一
  comments: ItemComment[];
  is_favorited: boolean;
  favorites_count: number;
};

export type ItemDisplayAttribute = {
  name: string | null;
  source?: "ai_provisional" | "human_confirmed";
  is_latest?: boolean;
};

export const useItemDetailSWR = (itemId: number | null) => {
  const { apiClient, isLoading, isAuthenticated } = useAuth();

  console.log("[ItemDetailSWR] itemId =", itemId);
  console.log("[ItemDetailSWR] isLoading =", isLoading);
  console.log("[ItemDetailSWR] isAuthenticated =", isAuthenticated);
  console.log("[ItemDetailSWR] apiClient =", apiClient);

  const shouldFetch =
    typeof itemId === "number" &&
    Number.isFinite(itemId) &&
    !isLoading && // ✅ 修正点（超重要）
    apiClient !== null;

  console.log("[ItemDetailSWR] shouldFetch =", shouldFetch);

  const swrKey = shouldFetch
    ? ["item-detail", itemId, isAuthenticated ? "auth" : "guest"]
    : null;

  console.log("[ItemDetailSWR] swrKey =", swrKey);

  const fetcher = async (): Promise<ItemDetailResponse> => {
    console.log("[ItemDetailSWR] FETCH START", `/items/${itemId}`);
    const res = await apiClient!.get<ItemDetailResponse>(`/items/${itemId}`);
    console.log("[ItemDetailSWR] FETCH RESPONSE", res);
    return res;
  };

  const { data, error, mutate } = useSWR<ItemDetailResponse>(swrKey, fetcher, {
    revalidateOnFocus: false,
    revalidateOnReconnect: false,
    revalidateIfStale: false,
    shouldRetryOnError: false,
  });

  console.log("[ItemDetailSWR] data =", data);
  console.log("[ItemDetailSWR] error =", error);

  // auth 切替時は key が変わるので自動再取得される
  useEffect(() => {
    console.log(
      "[ItemDetailSWR] auth change",
      "isAuthenticated =",
      isAuthenticated,
      "isLoading =",
      isLoading
    );
  }, [isAuthenticated, isLoading]);

  return {
    item: data?.item ?? null,
    comments: data?.comments ?? [],
    isFavorited: data?.is_favorited ?? false,
    favoritesCount: data?.favorites_count ?? 0,
    isLoading,
    isError: error,
    mutateItemDetail: mutate,
  };
};
