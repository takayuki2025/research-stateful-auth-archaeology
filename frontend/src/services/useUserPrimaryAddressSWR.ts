import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";
import { useAuthedFetcher } from "@/ui/auth/useAuthedFetcher";

export type UserPrimaryAddress = {
  id: number;
  postNumber: string;
  prefecture: string;
  city: string;
  addressLine1: string;
  addressLine2?: string | null;
  recipientName: string;
};

export function useUserPrimaryAddressSWR() {
  const { isAuthenticated, isReady } = useAuth();
  const fetcher = useAuthedFetcher();

  const swrKey = isReady && isAuthenticated ? "/me/addresses/primary" : null;

  const { data, error, isLoading } = useSWR<UserPrimaryAddress | null>(
    swrKey,
    async () => {
      const res = await fetcher.get<any>("/me/addresses/primary");
      const a = res?.data ?? null;

      if (!a) return null;

      return {
        id: a.id,
        postNumber: a.post_number,
        prefecture: a.prefecture,
        city: a.city,
        addressLine1: a.address_line1,
        addressLine2: a.address_line2 ?? null,
        recipientName: a.recipient_name,
      };
    },
    {
      revalidateOnFocus: false,
    }
  );

  return {
    address: data ?? null,
    isLoading,
    isError: !!error,
  };
}
