import useSWR from "swr";
import type { Item } from "@/types/item";
import { useAuth } from "@/ui/auth/AuthProvider";

type ItemsResponse = {
  items: Item[];
};

export function useItemsSWR(search: string) {
  const { apiClient, isLoading, isAuthenticated } = useAuth();

  const params = new URLSearchParams();
  if (search) params.append("search", search);

  const url = `/items${params.toString() ? `?${params.toString()}` : ""}`;

  const swrKey =
    !isLoading && apiClient !== null
      ? ["items", search, isAuthenticated ? "auth" : "guest"]
      : null;

  const fetcher = async (): Promise<ItemsResponse> => {
    // この時点で apiClient は non-null
    return apiClient!.get<ItemsResponse>(url);
  };

  const {
    data,
    error,
    isLoading: swrLoading,
    mutate,
  } = useSWR<ItemsResponse>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    isError: !!error,
    mutate,
  };
}
