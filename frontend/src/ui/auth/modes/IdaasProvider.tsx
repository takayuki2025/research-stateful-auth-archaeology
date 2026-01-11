"use client";

import React, {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";
import type { AuthContext } from "@/ui/auth/contracts";
import type { AuthUser } from "@/domain/auth/AuthUser";
import { createSanctumApiClient } from "@/ui/auth/sanctum/SanctumApiClient";

/**
 * ⚠️ これは「空実装テンプレ」
 * - 実際の OIDC / SAML / PKCE / Token Exchange はまだ入れない
 * - /me 契約だけは最終形を使う
 */

const AuthCtx = createContext<AuthContext | null>(null);

export default function IdaasProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const apiClient = useMemo(() => createSanctumApiClient(), []);
  const [user, setUser] = useState<AuthUser | null>(null);
  const [authReady, setAuthReady] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // 起動時：SSO済み前提で /me を叩く想定
  useEffect(() => {
    let mounted = true;
    (async () => {
      try {
        const u = await apiClient.get<AuthUser>("/me");
        if (mounted) setUser(u);
      } catch {
        if (mounted) setUser(null);
      } finally {
        if (mounted) {
          setIsLoading(false);
          setAuthReady(true);
        }
      }
    })();
    return () => {
      mounted = false;
    };
  }, [apiClient]);

  const value: AuthContext = useMemo(
    () => ({
      isLoading,
      authReady,
      isAuthenticated: !!user,
      user,
      apiClient,

      // ★ 企業SSOでは login は「リダイレクト」になる
      login: async () => {
        window.location.href = "/auth/sso/redirect"; // 仮
      },

      logout: async () => {
        await apiClient.post("/logout");
        setUser(null);
        window.location.href = "/login";
      },

      refresh: async () => {
        const u = await apiClient.get<AuthUser>("/me");
        setUser(u);
      },
    }),
    [isLoading, authReady, user, apiClient]
  );

  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}

export function useAuth(): AuthContext {
  const ctx = useContext(AuthCtx);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
