import useSWR from "swr";
import type { Item } from "@/types/item";
import { useAuth } from "@/ui/auth/AuthProvider";

type Response = {
  items: Item[];
};

export const useShopItemsSWR = (shopId: number | null) => {
  const { apiClient, isAuthenticated, isLoading } = useAuth();

  // ✅ isReady 廃止 → isLoading + apiClient
  const shouldFetch =
    typeof shopId === "number" && !isLoading && apiClient !== null;

  const swrKey = shouldFetch
    ? ["shop-items", shopId, isAuthenticated ? "auth" : "guest"]
    : null;

  const fetcher = async (): Promise<Response> => {
    // ここに来た時点で apiClient は non-null
    return apiClient!.get<Response>(`/shops/${shopId}/items`);
  };

  const {
    data,
    isLoading: swrLoading,
    error,
  } = useSWR<Response>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    isError: !!error,
  };
};
