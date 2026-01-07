import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { publicClient } from "@/infrastructure/http/publicClient";


export function useItemListByShopSWR(shopCode?: string) {
  const canFetch =
    typeof shopCode === "string" &&
    shopCode.length > 0;

  const key = canFetch ? ["shop-items", shopCode] : null;

  const fetcher = async () => {
    const res = await publicClient.get(
      `/shops/${shopCode}/items`
    );
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