import { useEffect } from "react";
import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
/**
 * API Response 型
 */
export type ItemDetailResponse = {
  item: {
    id: number;
    shop_id: number;
    name: string;
    price: number;
    explain: string;
    remain: number;

    brands: string[];
    brand_primary: string | null;
    condition: string | null;
    color: string | null;
    categories: string[];

    tags: Record<string, any[]>;
    item_image: string | null;
  };

  comments: {
    id: number;
    comment: string;
    created_at: string;
    user: {
      id: number;
      name: string;
      user_image: string | null;
    };
  }[];

  is_favorited: boolean;
  favorites_count: number;
};

export const useItemDetailSWR = (itemId: number | null) => {
  const { apiClient, isReady, isAuthenticated } = useAuth();

  const shouldFetch =
    typeof itemId === "number" && Number.isFinite(itemId) && isReady;

  const swrKey = shouldFetch
    ? ["item-detail", itemId, isAuthenticated ? "auth" : "guest"]
    : null;

  const fetcher = async (): Promise<ItemDetailResponse> => {
    const res = await apiClient.get(`/items/${itemId}`);
    return res.data;
  };

  const { data, error, isLoading, mutate } = useSWR<ItemDetailResponse>(
    swrKey,
    fetcher,
    {
      revalidateOnFocus: false,
      revalidateOnReconnect: false,
      revalidateIfStale: false,
      shouldRetryOnError: false,
    }
  );

  // guest → auth 切替時の再取得
  useEffect(() => {
    if (isAuthenticated && isReady) {
      mutate();
    }
  }, [isAuthenticated, isReady, mutate]);

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