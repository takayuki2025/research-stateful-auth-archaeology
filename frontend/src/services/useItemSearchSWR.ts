import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { SearchItem } from "@/types/searchItem";

type ItemSearchResponse = {
  items: SearchItem[];
};

export const useItemSearchSWR = (query: string) => {
  const { isAuthenticated, isLoading: authLoading, isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const shouldFetch = isReady && query.trim().length > 0;

  const key = shouldFetch
    ? ["search-items", query, isAuthenticated ? "auth" : "guest"]
    : null;

  const { data, error, isLoading } = useSWR<ItemSearchResponse>(key, () =>
    fetcher.get(`/search/items?q=${encodeURIComponent(query)}`)
  );

  return {
    items: data?.items ?? [],
    isLoading: authLoading || isLoading,
    error,
  };
};
