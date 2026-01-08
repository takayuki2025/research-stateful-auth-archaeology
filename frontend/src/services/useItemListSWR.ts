import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import type { PublicItemSummary } from "@/types/publicItemSummary";

type Response = {
  items: PublicItemSummary[];
};

export const useItemListSWR = () => {
  const { apiClient, user, isReady } = useAuth();

  const swrKey = isReady ? ["public-items", user?.id ?? "guest"] : null;

  const fetcher = async (): Promise<Response> => {
    if (!apiClient) {
      throw new Error("apiClient not ready");
    }

    const url = user
      ? `/api/items/public?viewer_user_id=${user.id}`
      : `/api/items/public`;

    const res = await apiClient.get<Response>(url);
    return res.data;
  };

  const { data, error, isLoading, mutate } = useSWR(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading,
    error,
    mutateItems: mutate,
  };
};
