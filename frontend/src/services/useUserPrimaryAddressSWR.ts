import useSWR from "swr";
import { useAuth } from "@/ui/auth/useAuth";

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
  const { apiClient, isAuthenticated } = useAuth();

  const shouldFetch = isAuthenticated && apiClient;

  const { data, error, isLoading } = useSWR(
    shouldFetch ? "/me/addresses/primary" : null,
    async (url) => {
      const res = await apiClient!.get(url);
      const a = res.data?.data;

      if (!a) return null;

      return {
        id: a.id,
        postNumber: a.post_number,
        prefecture: a.prefecture,
        city: a.city,
        addressLine1: a.address_line1,
        addressLine2: a.address_line2,
        recipientName: a.recipient_name,
      } as UserPrimaryAddress;
    },
    {
      revalidateOnFocus: false,
    },
  );

  return {
    address: data ?? null,
    isLoading,
    isError: !!error,
  };
}
