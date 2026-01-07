import useSWR from "swr";
import { publicClient } from "@/infrastructure/http/publicClient";
import type { PublicItem } from "@/types/publicItem";

type ItemSearchResponse = {
  items: PublicItem[];
};

export function useItemSearchByShopSWR(shopCode?: string, query?: string) {
  const canFetch =
    typeof shopCode === "string" &&
    shopCode.length > 0 &&
    typeof query === "string" &&
    query.trim().length > 0;

  const key = canFetch ? ["shop-item-search", shopCode, query] : null;

  const fetcher = async (): Promise<ItemSearchResponse> => {
    const res = await publicClient.get(`/search/shop-items`, {
      params: {
        shop_code: shopCode,
        keyword: query,
      },
    });
    return res.data;
  };

  const { data, isLoading, error } = useSWR(key, fetcher, {
    revalidateOnFocus: false,
  });

  return {
    items: data?.items ?? [],
    isLoading,
    error,
  };
}
