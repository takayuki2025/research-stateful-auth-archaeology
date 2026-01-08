import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const useItemSearchSWR = (query: string) => {
  const { isLoading } = useAuth(); // ✅ isReady 廃止
  const authed = useAuthedFetcher();

  const shouldFetch =
    !isLoading && // ✅ Auth 初期化完了
    authed.isLoading && // apiClient ready
    query.trim().length > 0;

  const swrKey = shouldFetch ? ["search-items", query] : null;

  const fetcher = async (): Promise<Response> => {
    return authed.get<Response>(`/search/items?q=${encodeURIComponent(query)}`);
  };

  const {
    data,
    error,
    isLoading: swrLoading,
  } = useSWR<Response>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    error,
  };
};
