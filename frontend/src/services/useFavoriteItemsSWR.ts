import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";

export const useFavoriteItemsSWR = () => {
  const { authReady, isAuthenticated } = useAuth(); // ★authReady を使う
  const fetcher = useAuthedFetcher();

  const swrKey = authReady && isAuthenticated ? FAVORITE_ITEMS_SWR_KEY : null; // ★ここが本体

  const { data, error, mutate } = useSWR<Response>(
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
    // ★「Auth判定中」はローディング扱い
    isLoading: !authReady,
    error,
    refetchFavorites: () => mutate(),
  };
};
