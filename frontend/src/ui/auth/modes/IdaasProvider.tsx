"use client";

import React, { useEffect, useMemo, useRef, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import type { AuthContext } from "@/ui/auth/contracts";
import type { AuthUser } from "@/domain/auth/AuthUser";
import { AuthCtx } from "@/ui/auth/core/AuthContextCore";

import { TokenStorage } from "@/infrastructure/auth/TokenStorage";

type ApiClient = {
  get<T>(url: string): Promise<T>;
  post<T>(url: string, body?: unknown): Promise<T>;
  patch<T>(url: string, body?: unknown): Promise<T>;
  delete<T>(url: string): Promise<T>;
};

function createBearerApiClient(): ApiClient {
  const base = process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost";
  const apiBase = `${base}/api`;

  const request = async <T,>(
    method: "GET" | "POST" | "PATCH" | "DELETE",
    url: string,
    body?: unknown,
  ): Promise<T> => {
    const fullUrl = url.startsWith("http")
      ? url
      : `${apiBase}${url.startsWith("/") ? "" : "/"}${url}`;

    const { accessToken } = TokenStorage.load();
    const headers: Record<string, string> = { Accept: "application/json" };
    if (accessToken) headers.Authorization = `Bearer ${accessToken}`;
    if (method !== "GET") headers["Content-Type"] = "application/json";

    const res = await fetch(fullUrl, {
      method,
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
    });

    if (!res.ok) {
      let msg = `Request failed: ${res.status}`;
      try {
        const ct = res.headers.get("content-type") || "";
        if (ct.includes("application/json")) {
          const j = await res.json();
          msg = j?.message ?? msg;
        } else {
          const t = await res.text();
          if (t) msg = t;
        }
      } catch {}
      const e: any = new Error(msg);
      e.status = res.status;
      throw e;
    }

    if (res.status === 204) return undefined as unknown as T;
    return res.json();
  };

  return {
    get: <T,>(url: string) => request<T>("GET", url),
    post: <T,>(url: string, body?: unknown) => request<T>("POST", url, body),
    patch: <T,>(url: string, body?: unknown) => request<T>("PATCH", url, body),
    delete: <T,>(url: string) => request<T>("DELETE", url),
  };
}

// PKCE helpers
function randomString(len = 64): string {
  const chars =
    "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._~";
  const bytes = new Uint8Array(len);
  crypto.getRandomValues(bytes);
  let out = "";
  for (let i = 0; i < len; i++) out += chars[bytes[i] % chars.length];
  return out;
}

function base64UrlEncode(bytes: ArrayBuffer): string {
  const b = new Uint8Array(bytes);
  let s = "";
  for (let i = 0; i < b.length; i++) s += String.fromCharCode(b[i]);
  return btoa(s).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/g, "");
}

async function sha256Base64Url(input: string): Promise<string> {
  const enc = new TextEncoder().encode(input);
  const digest = await crypto.subtle.digest("SHA-256", enc);
  return base64UrlEncode(digest);
}

function buildCognitoEndpoints(domain: string) {
  const d = domain.replace(/\/+$/, "");
  return {
    authorize: `${d}/oauth2/authorize`,
    token: `${d}/oauth2/token`,
    logout: `${d}/logout`,
  };
}

const PKCE_VERIFIER_KEY = "oidc_pkce_verifier_v1";
const OIDC_STATE_KEY = "oidc_state_v1";
const OIDC_RETURN_TO_KEY = "oidc_return_to_v1";

export default function IdaasProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [isLoading, setIsLoading] = useState(true);
  const [authReady, setAuthReady] = useState(false);
  const [user, setUser] = useState<AuthUser | null>(null);

  const apiClient = useMemo(() => createBearerApiClient(), []);

  const domain = process.env.NEXT_PUBLIC_COGNITO_DOMAIN ?? "";
  const clientId = process.env.NEXT_PUBLIC_COGNITO_CLIENT_ID ?? "";
  const redirectUri =
    process.env.NEXT_PUBLIC_OIDC_REDIRECT_URI ??
    "http://localhost/oidc/callback";
  const postLogoutRedirectUri =
    process.env.NEXT_PUBLIC_OIDC_POST_LOGOUT_REDIRECT_URI ??
    "http://localhost/login";
  const scopes = process.env.NEXT_PUBLIC_OIDC_SCOPES ?? "openid email profile";

  const endpoints = useMemo(() => buildCognitoEndpoints(domain), [domain]);
  const exchangeInFlight = useRef(false);

  const reloadMe = async () => {
    try {
      const u = await apiClient.get<AuthUser>("/me");
      setUser(u);
    } catch {
      setUser(null);
    }
  };

  useEffect(() => {
    (async () => {
      try {
        const code = searchParams.get("code");
        const state = searchParams.get("state");
        const error = searchParams.get("error");

        if (error) {
          TokenStorage.clear();
          setUser(null);
          return;
        }

        // callback: code -> tokens
        if (code && !exchangeInFlight.current) {
          exchangeInFlight.current = true;

          const expectedState = sessionStorage.getItem(OIDC_STATE_KEY);
          sessionStorage.removeItem(OIDC_STATE_KEY);

          if (!expectedState || state !== expectedState) {
            TokenStorage.clear();
            router.replace("/login");
            return;
          }

          const verifier = sessionStorage.getItem(PKCE_VERIFIER_KEY);
          sessionStorage.removeItem(PKCE_VERIFIER_KEY);

          if (!verifier) {
            TokenStorage.clear();
            router.replace("/login");
            return;
          }

          const body = new URLSearchParams();
          body.set("grant_type", "authorization_code");
          body.set("client_id", clientId);
          body.set("code", code);
          body.set("redirect_uri", redirectUri);
          body.set("code_verifier", verifier);

          const res = await fetch(endpoints.token, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
              Accept: "application/json",
            },
            body: body.toString(),
          });

          if (!res.ok) {
            TokenStorage.clear();
            router.replace("/login");
            return;
          }

          const json = await res.json();
          const idToken = json.id_token as string | undefined;
          const accessToken = json.access_token as string | undefined;
          const refreshToken = (json.refresh_token as string | undefined) ?? "";

          const bearer = idToken ?? accessToken;
          if (!bearer) {
            TokenStorage.clear();
            router.replace("/login");
            return;
          }

          TokenStorage.save({
            accessToken: bearer, // token_use=id を優先
            refreshToken,
          });

          const returnTo = sessionStorage.getItem(OIDC_RETURN_TO_KEY) ?? "/";
          sessionStorage.removeItem(OIDC_RETURN_TO_KEY);

          router.replace(returnTo);
          return;
        }

        // normal init
        const { accessToken } = TokenStorage.load();
        if (accessToken) {
          await reloadMe();
        }
      } finally {
        setIsLoading(false);
        setAuthReady(true);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchParams]);

  // AuthContext signature 互換：args を受けて無視し、PKCEリダイレクト
  const login = async (_args: { email: string; password: string }) => {
    if (!domain || !clientId) {
      throw new Error(
        "OIDC env missing: NEXT_PUBLIC_COGNITO_DOMAIN / NEXT_PUBLIC_COGNITO_CLIENT_ID",
      );
    }

    const verifier = randomString(64);
    const challenge = await sha256Base64Url(verifier);

    sessionStorage.setItem(PKCE_VERIFIER_KEY, verifier);

    const state = randomString(32);
    sessionStorage.setItem(OIDC_STATE_KEY, state);

    // login page からの復帰先（必要なら拡張）
    sessionStorage.setItem(OIDC_RETURN_TO_KEY, "/");

    const params = new URLSearchParams();
    params.set("response_type", "code");
    params.set("client_id", clientId);
    params.set("redirect_uri", redirectUri);
    params.set("scope", scopes);
    params.set("code_challenge", challenge);
    params.set("code_challenge_method", "S256");
    params.set("state", state);

    window.location.assign(`${endpoints.authorize}?${params.toString()}`);
  };

  const logout = async () => {
    TokenStorage.clear();
    setUser(null);

    if (!domain || !clientId) {
      router.replace("/login");
      return;
    }

    const params = new URLSearchParams();
    params.set("client_id", clientId);
    params.set("logout_uri", postLogoutRedirectUri);

    window.location.assign(`${endpoints.logout}?${params.toString()}`);
  };

  const value: AuthContext = useMemo(
    () => ({
      isLoading,
      authReady,
      isAuthenticated: !!user,
      user,
      apiClient,

      login,
      logout,
      refresh: reloadMe,
    }),
    [isLoading, authReady, user, apiClient],
  );

  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}
