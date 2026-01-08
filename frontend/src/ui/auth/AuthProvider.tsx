"use client";

import React, {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import type { AuthContext, AuthUser } from "@/ui/auth/contracts";
import { SanctumAuthAdapter } from "@/ui/auth/sanctum/SanctumAuthAdapter";

const AuthCtx = createContext<AuthContext | null>(null);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const adapter = useMemo(() => new SanctumAuthAdapter(), []);

  const [isLoading, setIsLoading] = useState(true);
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
      }
    })();
    return () => {
      mounted = false;
    };
  }, [adapter]);

  const value: AuthContext = useMemo(
    () => ({
      isLoading,
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
      },
    }),
    [adapter, isLoading, user]
  );

  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}

export function useAuth(): AuthContext {
  const ctx = useContext(AuthCtx);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
