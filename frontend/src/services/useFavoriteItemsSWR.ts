import useSWR from "swr";
import type { AxiosInstance } from "axios";

import { useAuth } from "@/ui/auth/useAuth";
import type { PublicItem } from "@/types/publicItem";

type FavoriteItemsResponse = {
  items: PublicItem[];
};

export const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";

export const useFavoriteItemsSWR = () => {
  const { apiClient, isAuthenticated, isLoading } = useAuth();

  const swrKey =
    !isLoading && isAuthenticated && apiClient ? "/items/favorite" : null;

  const fetcher = async () => {
    const res = await apiClient!.get("/items/favorite");
    return res.data;
  };

  const {
    data,
    error,
    isLoading: swrLoading,
    mutate,
  } = useSWR(swrKey, fetcher, {
    revalidateOnFocus: false,
    revalidateOnReconnect: false,
    revalidateIfStale: false,
  });

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    error,

    /** ★ これだけ使う */
    refetchFavorites: () => mutate(),
  };
};