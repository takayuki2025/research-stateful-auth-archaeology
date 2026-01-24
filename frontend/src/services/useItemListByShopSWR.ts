import useSWR from "swr";
import { publicClient } from "@/infrastructure/http/publicClient";

export function useItemListByShopSWR(shopCode?: string) {
  const canFetch = typeof shopCode === "string" && shopCode.length > 0;
  const key = canFetch ? ["shop-items", shopCode] : null;

  const fetcher = async () => {
    // ✅ api.php の Route::prefix('shops/{shop_code}') に対応するのは /api/shops/...
    const res = await publicClient.get(`/api/shops/${shopCode}/items`);
    return res.data;
  };

  const { data, isLoading, error } = useSWR(key, fetcher, {
    revalidateOnFocus: false,
    revalidateOnReconnect: false,
  });

  return {
    items: data?.items ?? [],
    isLoading,
    error,
  };
}
