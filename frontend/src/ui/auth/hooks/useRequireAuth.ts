"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../useAuth";

export function useRequireAuth(redirectTo = "/login") {
  const router = useRouter();
  const { isReady, isLoading, isAuthenticated } = useAuth();

  useEffect(() => {
    if (!isReady || isLoading) return;
    if (!isAuthenticated) {
      router.replace(redirectTo);
    }
  }, [isReady, isLoading, isAuthenticated, redirectTo, router]);

  return { isReady, isLoading, isAuthenticated };
}