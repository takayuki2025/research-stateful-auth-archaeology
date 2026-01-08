"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

export function useProfileGate(options?: {
  profileUrl?: string;
  redirectTo?: string;
}) {
  const profileUrl = options?.profileUrl ?? "/mypage/profile";
  const redirectTo = options?.redirectTo ?? "/mypage/profile";

  const router = useRouter();
  const { isAuthenticated, isLoading, apiClient } = useAuth();

  const [hasProfile, setHasProfile] = useState<boolean | null>(null);

  // 🔹 プロフィール有無の取得（ログイン済み時のみ）
  useEffect(() => {
    if (isLoading || !isAuthenticated) return;

    let cancelled = false;

    (async () => {
      try {
        const data = await apiClient.get<any>(profileUrl);
        if (!cancelled) {
          setHasProfile(!!data?.has_profile);
        }
      } catch {
        if (!cancelled) {
          setHasProfile(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [isLoading, isAuthenticated, apiClient, profileUrl]);

  // 🔹 未作成ならリダイレクト
  useEffect(() => {
    if (isAuthenticated && hasProfile === false) {
      router.replace(redirectTo);
    }
  }, [isAuthenticated, hasProfile, redirectTo, router]);

  // 🔹 Gate のローディング状態
  const isGateLoading = useMemo(() => {
    if (!isAuthenticated) return false;
    return hasProfile === null;
  }, [isAuthenticated, hasProfile]);

  return {
    isGateLoading,
    hasProfile,
  };
}
