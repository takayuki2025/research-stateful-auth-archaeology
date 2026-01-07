import useSWR from "swr";
import { PublicItem } from "@/types/publicItem";
import { useAuth } from "@/ui/auth/useAuth";

type Response = { items: PublicItem[] };

export const useItemListSWR = () => {
  const { apiClient, user, isReady } = useAuth();

  const swrKey = isReady ? ["public-items", user?.id ?? "guest"] : null;

  const fetcher = async (): Promise<Response> => {
    if (!apiClient) {
      throw new Error("apiClient not ready");
    }

    const url = user
      ? `/items/public?viewer_user_id=${encodeURIComponent(user.id)}`
      : `/items/public`;

    const res = await apiClient.get<Response>(url);
    return res.data;
  };

  const { data, error, isLoading, mutate } = useSWR<Response>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading,
    error,
    mutateItems: mutate,
  };
};
