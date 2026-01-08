import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const useItemSearchSWR = (query: string) => {
  const { isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const shouldFetch = isReady && query.trim().length > 0;

  const swrKey = shouldFetch ? ["search-items", query] : null;

  const { data, error, isLoading } = useSWR<Response>(swrKey, () =>
    fetcher.get(`/search/items?q=${encodeURIComponent(query)}`)
  );

  return {
    items: data?.items ?? [],
    isLoading,
    error,
  };
};
