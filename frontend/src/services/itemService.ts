import useSWR from "swr";
import type { Item } from "@/types/item";
import { useAuth } from "@/ui/auth/useAuth";

export function useItemsSWR(search: string) {
  const { apiClient, isReady, isAuthenticated } = useAuth();

  const params = new URLSearchParams();
  if (search) params.append("search", search);

  const url = `/items${params.toString() ? `?${params.toString()}` : ""}`;

  const swrKey = isReady
    ? ["items", search, isAuthenticated ? "auth" : "guest"]
    : null;

  const fetcher = async () => {
    const res = await apiClient.get(url);
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

