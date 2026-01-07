import useSWR from "swr";
import type { Item } from "@/types/item";
import { useAuth } from "@/ui/auth/useAuth";

export const useShopItemsSWR = (shopId: number | null) => {
  const { apiClient, isReady, isAuthenticated } = useAuth();

  const shouldFetch = typeof shopId === "number" && isReady;

  const swrKey = shouldFetch
    ? ["shop-items", shopId, isAuthenticated ? "auth" : "guest"]
    : null;

  const fetcher = async () => {
    const res = await apiClient.get(`/shops/${shopId}/items`);
    return res.data;
  };

  const { data, isLoading, error } = useSWR(swrKey, fetcher);

  return {
    items: (data?.items ?? []) as Item[],
    isLoading,
    isError: error,
  };
};