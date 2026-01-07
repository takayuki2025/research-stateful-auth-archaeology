import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";

export type UserAddress = {
  id: number;
  postNumber: string;
  address: string;
  building: string | null;
};

export type UserProfile = {
  address: UserAddress | null;
};

export function useUserProfileSWR() {
  const { apiClient, isAuthenticated } = useAuth();

  const { data, error, isLoading } = useSWR(
    isAuthenticated && apiClient ? "/mypage/profile" : null,
    async (url) => {
      const res = await apiClient!.get(url);

      /**
       * バックエンドはこう返す前提：
       * {
       *   address: {
       *     id,
       *     post_number,
       *     address,
       *     building
       *   }
       * }
       */
      const a = res.data.address ?? null;

      if (!a) {
        return { address: null };
      }

      return {
        address: {
          id: a.id,
          postNumber: a.post_number,
          address: a.address,
          building: a.building ?? null,
        },
      };
    },
  );

  return {
    profile: data ?? null,
    isLoading,
    isError: !!error,
  };
}
