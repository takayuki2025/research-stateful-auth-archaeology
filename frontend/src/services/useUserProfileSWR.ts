import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";

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
  const { isAuthenticated, isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const swrKey = isReady && isAuthenticated ? "/mypage/profile" : null;

  const { data, error, isLoading } = useSWR<UserProfile>(swrKey, async () => {
    const data = await fetcher.get<any>("/mypage/profile");

    /**
     * backend response:
     * {
     *   address: {
     *     id,
     *     post_number,
     *     address,
     *     building
     *   }
     * }
     */
    const a = data.address ?? null;

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
  });

  return {
    profile: data ?? null,
    isLoading,
    isError: !!error,
  };
}
