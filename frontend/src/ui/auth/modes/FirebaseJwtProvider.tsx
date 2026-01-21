"use client";

import React, { useEffect, useMemo, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import type { AuthContext } from "@/ui/auth/contracts";
import type { AuthUser } from "@/domain/auth/AuthUser";
import { AuthCtx } from "@/ui/auth/core/AuthContextCore";

import { AuthService } from "@/application/auth/AuthService";
import { FirebaseAuthClient } from "@/infrastructure/auth/FirebaseAuthClient";
import { LaravelAuthApi } from "@/infrastructure/auth/LaravelAuthApi";
import { createHttpClient } from "@/infrastructure/auth/HttpClient";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";
import { createFirebaseJwtApiClient } from "@/ui/auth/firebaseJwt/FirebaseJwtApiClient";

// ✅ Firebase modular SDK: "firebase is not defined" 対策
import { getAuth } from "firebase/auth";

export default function FirebaseJwtProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();

  const [isLoading, setIsLoading] = useState(true);
  const [authReady, setAuthReady] = useState(false);
  const [user, setUser] = useState<AuthUser | null>(null);

  const authServiceRef = useRef<AuthService | null>(null);
  const laravelApiRef = useRef<LaravelAuthApi | null>(null);

  // contracts準拠のApiClient（fetch + Bearer）
  const apiClient = useMemo(() => createFirebaseJwtApiClient(), []);

  // =========================================================
  // ✅ DEBUG: Firebase ID token の "iss/aud/sub/email" だけを出す（安全）
  // - 使い終わったらこの関数をまるごと削除してください
  // - token本文は原則出さない（漏洩防止）
  // - ただし curl テスト用に after_login のときだけ COPY で出す（終わったら必ず削除）
  // =========================================================
  const debugPrintFirebaseIdToken = async (label: string) => {
    try {
      const auth = getAuth();
      const current = auth.currentUser;

      if (!current) {
        console.log(`[FIREBASE_ID_TOKEN][${label}] currentUser is null`);
        return;
      }

      const idToken = await current.getIdToken(true);

      const dotCount = (idToken.match(/\./g) || []).length;
      console.log(
        `[FIREBASE_ID_TOKEN][${label}] length=${idToken.length} dot_count=${dotCount}`,
      );

      // ✅ curl テスト用：ログイン直後だけ token 本体を出す（終わったら削除）
      if (label === "after_login") {
        console.log("[FIREBASE_ID_TOKEN][after_login][COPY]", idToken);
      }

      if (dotCount !== 2) {
        console.log(`[FIREBASE_ID_TOKEN][${label}] NOT_JWT_FORMAT`);
        return;
      }

      const [, payloadB64] = idToken.split(".");
      const payloadJson = atob(
        payloadB64.replace(/-/g, "+").replace(/_/g, "/"),
      );
      const payload = JSON.parse(payloadJson);

      console.log(`[FIREBASE_ID_TOKEN][${label}] iss=`, payload.iss);
      console.log(`[FIREBASE_ID_TOKEN][${label}] aud=`, payload.aud);
      console.log(`[FIREBASE_ID_TOKEN][${label}] sub=`, payload.sub);
      console.log(`[FIREBASE_ID_TOKEN][${label}] email=`, payload.email);

      const signInProvider = payload?.firebase?.sign_in_provider;
      if (signInProvider) {
        console.log(
          `[FIREBASE_ID_TOKEN][${label}] firebase.sign_in_provider=`,
          signInProvider,
        );
      }
    } catch (e) {
      console.log(`[FIREBASE_ID_TOKEN][${label}] failed`, e);
    }
  };

  useEffect(() => {
    // NOTE: FirebaseAuthClient の constructor が引数必須なら、
    // ここも同様に (config/auth) を渡す必要があります。
    const firebase = new FirebaseAuthClient();

    // 旧LaravelAuthApiはaxios想定っぽいので、ここは「移行期の最小動作」として残して良い。
    const api = new LaravelAuthApi(null);

    // ✅ 引数必須：refreshService は今フェーズ未使用なので null を渡す
    const client = createHttpClient(null);
    api.setClient(client);

    const auth = new AuthService(firebase, api);

    laravelApiRef.current = api;
    authServiceRef.current = auth;

    const { accessToken } = TokenStorage.load();
    if (!accessToken) {
      setIsLoading(false);
      setAuthReady(true);
      return;
    }

    (async () => {
      try {
        const u = await apiClient.get<AuthUser>("/me");
        setUser(u);

        // ✅ DEBUG: 既にログイン済みならトークンを出す
        await debugPrintFirebaseIdToken("init");
      } catch (e: any) {
        TokenStorage.clear();
        setUser(null);
      } finally {
        setIsLoading(false);
        setAuthReady(true);
      }
    })();
  }, [apiClient]);

  const refresh = async () => {
    try {
      const u = await apiClient.get<AuthUser>("/me");
      setUser(u);

      // ✅ DEBUG: refresh 後の token も出す（不要なら削除）
      await debugPrintFirebaseIdToken("refresh");
    } catch {
      setUser(null);
    }
  };

  const value: AuthContext = useMemo(
    () => ({
      isLoading,
      authReady,
      isAuthenticated: !!user,
      user,
      apiClient,

      login: async ({ email, password }) => {
        const auth = authServiceRef.current;
        if (!auth) throw new Error("AuthService not ready");

        setIsLoading(true);
        try {
          await auth.login({ email, password });

          // ✅ DEBUG: login 完了直後に token を出す（最重要ポイント）
          await debugPrintFirebaseIdToken("after_login");

          await refresh();
        } finally {
          setIsLoading(false);
        }
      },

      logout: async () => {
        const auth = authServiceRef.current;
        if (auth) await auth.logout();
        TokenStorage.clear();
        setUser(null);
        router.replace("/login");
      },

      refresh,
    }),
    [isLoading, authReady, user, apiClient, router],
  );

  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}
