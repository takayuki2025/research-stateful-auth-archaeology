import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";

export const useFavoriteItemsSWR = () => {
  const { isAuthenticated, isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const swrKey = isReady && isAuthenticated ? FAVORITE_ITEMS_SWR_KEY : null;

  const { data, error, isLoading, mutate } = useSWR<Response>(
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
    isLoading,
    error,
    refetchFavorites: () => mutate(),
  };
};
