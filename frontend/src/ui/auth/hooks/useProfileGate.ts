"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../useAuth";

export function useProfileGate(options?: {
  profileUrl?: string;
  redirectTo?: string;
}) {
  const profileUrl = options?.profileUrl ?? "/mypage/profile";
  const redirectTo = options?.redirectTo ?? "/mypage/profile";

  const router = useRouter();
  const { isAuthenticated, isReady, isLoading, authClient } = useAuth();

  const [profileChecked, setProfileChecked] = useState(false);
  const [hasProfile, setHasProfile] = useState<boolean | null>(null);

  useEffect(() => {
    if (!isReady || isLoading) return;
    if (!isAuthenticated) {
      setProfileChecked(true);
      setHasProfile(null);
      return;
    }

    let cancelled = false;

    (async () => {
      try {
        const data = await authClient.get<any>(profileUrl);
        if (cancelled) return;
        setHasProfile(!!data?.has_profile);
        setProfileChecked(true);
      } catch {
        if (cancelled) return;
        setHasProfile(false);
        setProfileChecked(true);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [isReady, isLoading, isAuthenticated, authClient, profileUrl]);

  useEffect(() => {
    if (isAuthenticated && profileChecked && hasProfile === false) {
      router.replace(redirectTo);
    }
  }, [isAuthenticated, profileChecked, hasProfile, redirectTo, router]);

  const isGateLoading = useMemo(
    () => isAuthenticated && (!profileChecked || hasProfile === null),
    [isAuthenticated, profileChecked, hasProfile]
  );

  return { isGateLoading, profileChecked, hasProfile };
}
