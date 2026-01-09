"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

/**
 * 🔐 Occ_Auth_v1 Auth Guard（最終形）
 */
export function useAuthGuard() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (isLoading) return;

    if (!isAuthenticated || !user) return;

    if (!user.email_verified_at) {
      router.replace("/email/verify");
      return;
    }

    if (!user.profile_completed) {
      router.replace("/mypage/profile");
      return;
    }
  }, [isLoading, isAuthenticated, user, router]);
}
