import { useCallback, useEffect, useState } from "react";
import { apiClient } from "../lib/apiClient";

type User = {
  id: number;
  name: string;
  email: string;
};

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [booting, setBooting] = useState(true);

  const fetchMe = useCallback(async () => {
    const res = await apiClient.get("/api/me");
    setUser(res.data as User);
  }, []);

  // 初期認証：/me を起点に確定
  useEffect(() => {
    (async () => {
      try {
        await fetchMe();
      } catch {
        setUser(null);
      } finally {
        setBooting(false);
      }
    })();
  }, [fetchMe]);

  const login = useCallback(
    async (email: string, password: string) => {
      // Sanctum SPA の “超重要” 3ステップ
      await apiClient.get("/sanctum/csrf-cookie");
      await apiClient.post("/login", { email, password });
      await fetchMe();
    },
    [fetchMe]
  );

  const logout = useCallback(async () => {
    await apiClient.post("/logout");
    setUser(null);
  }, []);

  return {
    user,
    isAuthenticated: !!user,
    booting,
    login,
    logout,
    refetch: fetchMe,
  };
}
