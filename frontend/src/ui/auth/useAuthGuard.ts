"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/useAuth";

/**
 * 🔐 Occ_Auth_v1 Auth Guard（最終形）
 */
export function useAuthGuard() {
  const { user, isAuthenticated, isLoading, isReady } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isReady || isLoading) return;

    // 未ログインはガードしない
    if (!isAuthenticated || !user) return;

    // ① メール未認証
    if (!user.email_verified_at) {
      router.replace("/email/verify");
      return;
    }

    // ② プロフィール未完了（唯一の判定）
    if (!user.profile_completed) {
      router.replace("/mypage/profile");
      return;
    }

    // ③ 通過
  }, [isReady, isLoading, isAuthenticated, user, router]);
}
