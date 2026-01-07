import useSWR from "swr";
import axios from "axios";
import type { AxiosInstance } from "axios";
import type { Item } from "@/types/item";

/* ============================================================
   商品一覧
   - all    : public / auth 両対応
   - mylist : auth 必須（favorites）
============================================================ */
export function useItemsSWR(search: string, apiClient: AxiosInstance | null) {
  const params = new URLSearchParams();
  if (search) params.append("search", search);

  const url = `/item${params.toString() ? `?${params.toString()}` : ""}`;

  const swrKey = ["items-public", url];

  const fetcher = async () => {
    if (apiClient) {
      const res = await apiClient.get(url);
      return res.data;
    }
    const res = await axios.get(`/api${url}`);
    return res.data;
  };

  const { data, isLoading, error, mutate } = useSWR(swrKey, fetcher);

  return {
    items: (data?.items ?? []) as Item[],
    isLoading,
    isError: error,
    mutate,
  };
}

/* ============================================================
   ショップ別 商品一覧
============================================================ */
export const useShopItemsSWR = (
  shopId: number | null,
  apiClient: AxiosInstance | null,
) => {
  const url = shopId ? `/shops/${shopId}/items` : null;

  const swrKey = url
    ? ["shop-items", shopId, apiClient ? "auth" : "public"]
    : null;

  const fetcher = async () => {
    if (!url) return null;

    if (apiClient) {
      const res = await apiClient.get(url);
      return res.data;
    }

    const res = await axios.get(`/api${url}`);
    return res.data;
  };

  const swr = useSWR(swrKey, fetcher);

  return {
    items: (swr.data?.items ?? []) as Item[],
    isLoading: swr.isLoading,
    isError: swr.error,
  };
};
