"use client";

import React, {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import { useRouter } from "next/navigation"; // ← 追加
import type { AuthContext, AuthUser } from "@/ui/auth/contracts";
import { SanctumAuthAdapter } from "@/ui/auth/sanctum/SanctumAuthAdapter";

const AuthCtx = createContext<AuthContext | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const router = useRouter(); // ← 追加
  const adapter = useMemo(() => new SanctumAuthAdapter(), []);

  const [isLoading, setIsLoading] = useState(true);
  const [authReady, setAuthReady] = useState(false);
  const [user, setUser] = useState<AuthUser | null>(null);

  useEffect(() => {
    let mounted = true;
    (async () => {
      setIsLoading(true);
      try {
        const u = await adapter.init();
        if (mounted) setUser(u);
      } finally {
        if (mounted) setIsLoading(false);
                    setAuthReady(true);
      }
    })();
    return () => {
      mounted = false;
    };
  }, [adapter]);

  const value: AuthContext = useMemo(
    () => ({
      isLoading,
      authReady, // ★ここを追加
      isAuthenticated: !!user,
      user,
      apiClient: adapter.getApiClient(),

      login: async ({ email, password }) => {
        await adapter.login({ email, password });
        const u = await adapter.init();
        setUser(u);
      },

      logout: async () => {
        await adapter.logout();
        setUser(null);
        router.replace("/login");
      },
    }),
    [adapter, isLoading, authReady, user, router] // ★authReady も依存に追加
  );


  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}

export function useAuth(): AuthContext {
  const ctx = useContext(AuthCtx);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
