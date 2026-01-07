import useSWR from "swr";
import axios from "axios";
import { PublicItem } from "@/types/publicItem";
import { useAuth } from "@/ui/auth/useAuth";

type Response = { items: PublicItem[] };

export const useItemListSWR = () => {
  const { apiClient, user, isLoading } = useAuth();

  const fetcher = async (): Promise<Response> => {
    // 🔐 認証済み
    if (apiClient && user) {
      const res = await apiClient.get("/items/public", {
        params: {
          viewer_user_id: user.id, // ★ これが本命
        },
      });
      return res.data;
    }

    // 👤 ゲスト
    const res = await axios.get("/api/items/public");
    return res.data;
  };

  const swrKey = isLoading
    ? null
    : user
      ? ["public-items", user.id]
      : ["public-items", "guest"];

  const {
    data,
    error,
    isLoading: swrLoading,
    mutate,
  } = useSWR<Response>(swrKey, fetcher);

  return {
    items: data?.items ?? [],
    isLoading: isLoading || swrLoading,
    error,
    mutateItems: mutate,
  };
};
