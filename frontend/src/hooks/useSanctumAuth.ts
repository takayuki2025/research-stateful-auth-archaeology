"use client";

import { useAuth } from "@/ui/auth/useAuth";

/**
 * 互換 shim:
 * 旧: useSanctumAuth/useApiClient に依存するコードを壊さずに維持する。
 * 新: AuthProvider 経由で mode ごとの apiClient を返す。
 */
export function useApiClient() {
  const { apiClient } = useAuth() as any;
  return apiClient;
}
