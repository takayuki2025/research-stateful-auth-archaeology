"use client";

import { useAuth } from "@/ui/auth/useAuth";

type AuthedFetcher = {
  isReady: boolean;
  isAuthenticated: boolean;

  get<T = any>(url: string): Promise<T>;
  post<T = any>(url: string, body?: any): Promise<T>;
  patch<T = any>(url: string, body?: any): Promise<T>;
  delete<T = any>(url: string): Promise<T>;
};

export function useAuthedFetcher(): AuthedFetcher {
  const { apiClient, isReady, isAuthenticated } = useAuth();

  // ★ 重要：未準備でも hook は返す（throwしない）
  const notReady = !isReady || !apiClient;

  return {
    isReady,
    isAuthenticated,

    async get<T>(url: string): Promise<T> {
      if (notReady) throw new Error("Auth not ready");
      const res = await apiClient.get<T>(url);
      return res.data;
    },

    async post<T>(url: string, body?: any): Promise<T> {
      if (notReady) throw new Error("Auth not ready");
      const res = await apiClient.post<T>(url, body);
      return res.data;
    },

    async patch<T>(url: string, body?: any): Promise<T> {
      if (notReady) throw new Error("Auth not ready");
      const res = await apiClient.patch<T>(url, body);
      return res.data;
    },

    async delete<T>(url: string): Promise<T> {
      if (notReady) throw new Error("Auth not ready");
      const res = await apiClient.delete<T>(url);
      return res.data;
    },
  };
}
