"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

/**
 * 🔐 Occ_Auth_v1 Auth Guard（v1: login直後の遷移競合を回避）
 */
export function useAuthGuard() {
  const { user, isAuthenticated, isLoading, authReady } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (isLoading) return;
    if (!authReady) return;

    // 未ログインなら何もしない（ページ側でログイン導線を出す）
    if (!isAuthenticated || !user) return;

    // ✅ login直後の遷移競合を避ける（dashboardへ飛ばす処理を邪魔しない）
    const justLoggedIn =
      typeof window !== "undefined" &&
      sessionStorage.getItem("occore_just_logged_in_v1") === "1";
    if (justLoggedIn) return;

    if (!user.email_verified_at) {
      router.replace("/email/verify");
      return;
    }

    if (!user.profile_completed) {
      router.replace("/mypage/profile");
      return;
    }
  }, [isLoading, authReady, isAuthenticated, user, router]);
}
