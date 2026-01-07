"use client";

import { useApiClient } from "@/hooks/useSanctumAuth";

export function useShopApi(shopCode: string) {
  const api = useApiClient();

  return {
    // 店舗の商品一覧
    async listItems() {
      const res = await api.get(`/shops/${shopCode}/items`);
      return res.data.items;
    },

    // 商品登録（OWNER専用）
    async createItem(payload: any) {
      const res = await api.post(`/shops/${shopCode}/items`, payload);
      return res.data;
    },
  };
}
