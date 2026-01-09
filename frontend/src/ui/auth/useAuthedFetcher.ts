"use client";

import { useAuth } from "@/ui/auth/AuthProvider";

export function useAuthedFetcher() {
  const { apiClient, isLoading, isAuthenticated } = useAuth();

  const notReady = isLoading || !apiClient;

  return {
    isLoading,
    isAuthenticated,

    get: <T>(url: string) => {
      if (notReady) throw new Error("Auth not ready");
      return apiClient.get<T>(url);
    },

    post: <T>(url: string, body?: any) => {
      if (notReady) throw new Error("Auth not ready");
      return apiClient.post<T>(url, body);
    },

    patch: <T>(url: string, body?: any) => {
      if (notReady) throw new Error("Auth not ready");
      return apiClient.patch<T>(url, body);
    },

    delete: <T>(url: string) => {
      if (notReady) throw new Error("Auth not ready");
      return apiClient.delete<T>(url);
    },
  };
}
