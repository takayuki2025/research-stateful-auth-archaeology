import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
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
  const { isAuthenticated, isLoading } = useAuth();
  const fetcher = useAuthedFetcher();

  // ✅ isReady は不要。isLoading で完全に代替できる
  const swrKey = !isLoading && isAuthenticated ? "/mypage/profile" : null;

  const {
    data,
    error,
    isLoading: swrLoading,
  } = useSWR<UserProfile>(swrKey, async () => {
    const res = await fetcher.get<any>("/mypage/profile");
    const a = res.address ?? null;

    if (!a) return { address: null };

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
    isLoading: isLoading || swrLoading,
    isError: !!error,
  };
}
