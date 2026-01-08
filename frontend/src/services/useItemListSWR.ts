import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const useItemListSWR = () => {
  const { apiClient, user, isLoading } = useAuth();

  const shouldFetch = !isLoading && apiClient !== null;

  const swrKey = shouldFetch ? ["public-items", user?.id ?? "guest"] : null;

  const fetcher = async (): Promise<Response> => {
    const url = user
      ? `/items/public?viewer_user_id=${user.id}`
      : `/items/public`;

    // ★ res はすでに Response
    return apiClient!.get<Response>(url);
  };

  const { data, error, mutate } = useSWR<Response>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading,
    error,
    mutateItems: mutate,
  };
};
