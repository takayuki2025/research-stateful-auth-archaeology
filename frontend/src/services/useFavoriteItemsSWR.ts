import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { PublicItem } from "@/types/publicItem";

type FavoriteItemsResponse = {
  items: PublicItem[];
};

export const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";

export const useFavoriteItemsSWR = () => {
  const { isAuthenticated, isLoading, isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const swrKey = isReady && isAuthenticated ? FAVORITE_ITEMS_SWR_KEY : null;

  const {
    data,
    error,
    isLoading: swrLoading,
    mutate,
  } = useSWR<FavoriteItemsResponse>(
    swrKey,
    () => fetcher.get(FAVORITE_ITEMS_SWR_KEY),
    {
      revalidateOnFocus: false,
      revalidateOnReconnect: false,
      revalidateIfStale: false,
    }
  );

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    error,
    refetchFavorites: () => mutate(),
  };
};
