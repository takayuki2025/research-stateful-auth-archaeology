"use client";

import React, {
  createContext,
  useCallback,
  useEffect,
  useMemo,
  useState,
} from "react";
import { SanctumClient } from "./clients/SanctumClient";
import type { AuthContextType, AxiosLikeClient } from "./AuthContext";
import type {
  AuthClient,
  AuthUser,
  LoginResult,
  RegisterResult,
} from "./AuthClient";

export const AuthContext = createContext<AuthContextType | null>(null);

function createAxiosLikeClient(authClient: AuthClient): AxiosLikeClient {
  return {
    async get(url) {
      const data = await authClient.get(url);
      return { data };
    },
    async post(url, body) {
      const data = await authClient.post(url, body);
      return { data };
    },
    async patch(url, body) {
      const data = await authClient.patch(url, body);
      return { data };
    },
    async delete(url) {
      const data = await authClient.delete(url);
      return { data };
    },
  };
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const authClient: AuthClient = SanctumClient;

  const apiClient = useMemo(
    () => createAxiosLikeClient(authClient),
    [authClient]
  );

  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isReady, setIsReady] = useState(false);

  const reloadUser = useCallback(async () => {
    const me = await authClient.me();
    setUser(me);
  }, [authClient]);

  useEffect(() => {
    (async () => {
      try {
        await reloadUser();
      } finally {
        setIsLoading(false);
        setIsReady(true);
      }
    })();
  }, [reloadUser]);

  const login = useCallback(
    async (args: { email: string; password: string }): Promise<LoginResult> => {
      setIsLoading(true);
      try {
        const result = await authClient.login(args.email, args.password);
        setUser(result.user);
        return result;
      } finally {
        setIsLoading(false);
      }
    },
    [authClient]
  );

  const register = useCallback(
    async (args: {
      name: string;
      email: string;
      password: string;
    }): Promise<RegisterResult> => {
      setIsLoading(true);
      try {
        return await authClient.register(args.name, args.email, args.password);
      } finally {
        setIsLoading(false);
      }
    },
    [authClient]
  );

  const logout = useCallback(async () => {
    setIsLoading(true);
    try {
      await authClient.logout();
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, [authClient]);

  const reloginWithFirebaseToken = useCallback(async () => {
    throw new Error("Not supported in Sanctum mode");
  }, []);

  const value: AuthContextType = useMemo(
    () => ({
      user,
      isAuthenticated: !!user,
      isLoading,
      isReady,
      authClient,
      apiClient,
      login,
      register,
      logout,
      reloadUser,
      reloginWithFirebaseToken,
    }),
    [
      user,
      isLoading,
      isReady,
      authClient,
      apiClient,
      login,
      register,
      logout,
      reloadUser,
      reloginWithFirebaseToken,
    ]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
