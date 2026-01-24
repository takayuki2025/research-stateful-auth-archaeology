"use client";

import { useEffect, useMemo, useRef, useState, useCallback } from "react";
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
      credentials: "omit", // JWT(Bearer)前提
    });

    if (!res.ok) {
      let msg = `Request failed: ${res.status}`;
      try {
        const ct = res.headers.get("content-type") || "";
        if (ct.includes("application/json")) {
          const j = await res.json().catch(() => ({}));
          msg = (j as any)?.message ?? msg;
        } else {
          const t = await res.text().catch(() => "");
          if (t) msg = t.slice(0, 300);
        }
      } catch {
        // ignore
      }
      const e: any = new Error(msg);
      e.status = res.status;
      throw e;
    }

    if (res.status === 204) return undefined as unknown as T;
    return (await res.json()) as T;
  };

  return {
    get: <T,>(url: string) => request<T>("GET", url),
    post: <T,>(url: string, body?: unknown) => request<T>("POST", url, body),
    patch: <T,>(url: string, body?: unknown) => request<T>("PATCH", url, body),
    delete: <T,>(url: string) => request<T>("DELETE", url),
  };
}

/* =========================================================
   PKCE helpers
========================================================= */
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

const PKCE_VERIFIER_KEY = "auth0_pkce_verifier_v1";
const OIDC_STATE_KEY = "auth0_state_v1";
const OIDC_RETURN_TO_KEY = "auth0_return_to_v1";

function clearOidcSessionState() {
  try {
    sessionStorage.removeItem(PKCE_VERIFIER_KEY);
    sessionStorage.removeItem(OIDC_STATE_KEY);
    sessionStorage.removeItem(OIDC_RETURN_TO_KEY);
  } catch {
    // ignore
  }
}

function safeReturnTo(raw: string | null | undefined): string {
  if (!raw) return "/";
  if (raw.startsWith("/")) return raw;
  return "/";
}

/* =========================================================
   Auth0 endpoints
========================================================= */
function buildAuth0Endpoints(domain: string) {
  const d = domain.replace(/^https?:\/\//, "").replace(/\/+$/, "");
  const origin = `https://${d}`;
  return {
    authorize: `${origin}/authorize`,
    token: `${origin}/oauth/token`,
    logout: `${origin}/v2/logout`,
  };
}

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

  const auth0Domain = process.env.NEXT_PUBLIC_AUTH0_DOMAIN ?? "";
  const clientId = process.env.NEXT_PUBLIC_AUTH0_CLIENT_ID ?? "";
  const audience = process.env.NEXT_PUBLIC_AUTH0_AUDIENCE ?? "";

  const redirectUri =
    process.env.NEXT_PUBLIC_OIDC_REDIRECT_URI ??
    "http://localhost/auth/callback";
  const postLogoutRedirectUri =
    process.env.NEXT_PUBLIC_OIDC_POST_LOGOUT_REDIRECT_URI ??
    "http://localhost/login";
  const scopes = process.env.NEXT_PUBLIC_OIDC_SCOPES ?? "openid profile email";

  const endpoints = useMemo(
    () => buildAuth0Endpoints(auth0Domain),
    [auth0Domain],
  );

  const exchangeInFlight = useRef(false);

  const fetchMe = useCallback(async (): Promise<AuthUser | null> => {
    try {
      const u = await apiClient.get<AuthUser>("/me");
      setUser(u);
      return u;
    } catch {
      setUser(null);
      return null;
    }
  }, [apiClient]);

  const refresh = useCallback(async (): Promise<void> => {
    await fetchMe();
  }, [fetchMe]);

  useEffect(() => {
    (async () => {
      try {
        const code = searchParams.get("code");
        const state = searchParams.get("state");
        const error = searchParams.get("error");
        const errorDescription = searchParams.get("error_description");

        if (error) {
          TokenStorage.clear();
          clearOidcSessionState();
          setUser(null);
          router.replace(
            `/login?oidc_error=${encodeURIComponent(error)}${
              errorDescription
                ? `&oidc_error_description=${encodeURIComponent(errorDescription)}`
                : ""
            }`,
          );
          return;
        }

        // callback exchange
        if (code && !exchangeInFlight.current) {
          exchangeInFlight.current = true;

          const expectedState = (() => {
            try {
              const v = sessionStorage.getItem(OIDC_STATE_KEY);
              sessionStorage.removeItem(OIDC_STATE_KEY);
              return v;
            } catch {
              return null;
            }
          })();

          if (!expectedState || state !== expectedState) {
            TokenStorage.clear();
            clearOidcSessionState();
            setUser(null);
            router.replace("/login?oidc_error=state_mismatch");
            return;
          }

          const verifier = (() => {
            try {
              const v = sessionStorage.getItem(PKCE_VERIFIER_KEY);
              sessionStorage.removeItem(PKCE_VERIFIER_KEY);
              return v;
            } catch {
              return null;
            }
          })();

          if (!verifier) {
            TokenStorage.clear();
            clearOidcSessionState();
            setUser(null);
            router.replace("/login?oidc_error=missing_verifier");
            return;
          }

          const body = new URLSearchParams();
          body.set("grant_type", "authorization_code");
          body.set("client_id", clientId);
          body.set("code", code);
          body.set("redirect_uri", redirectUri);
          body.set("code_verifier", verifier);

          // Auth0でAPI access token を取るには audience が必須
          if (audience) body.set("audience", audience);

          const res = await fetch(endpoints.token, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
              Accept: "application/json",
            },
            body: body.toString(),
          });

          if (!res.ok) {
            const text = await res.text().catch(() => "");
            TokenStorage.clear();
            clearOidcSessionState();
            setUser(null);
            router.replace(
              `/login?oidc_error=token_exchange_failed&status=${res.status}${
                text ? `&detail=${encodeURIComponent(text.slice(0, 200))}` : ""
              }`,
            );
            return;
          }

          const json = (await res.json().catch(() => ({}))) as any;

          const accessToken =
            typeof json?.access_token === "string" ? json.access_token : "";
          const refreshToken =
            typeof json?.refresh_token === "string" ? json.refresh_token : "";

          if (!accessToken) {
            TokenStorage.clear();
            clearOidcSessionState();
            setUser(null);
            router.replace("/login?oidc_error=missing_access_token");
            return;
          }

          TokenStorage.save({ accessToken, refreshToken });

          const me = await fetchMe();

          const returnTo = (() => {
            try {
              const v = sessionStorage.getItem(OIDC_RETURN_TO_KEY);
              sessionStorage.removeItem(OIDC_RETURN_TO_KEY);
              return v;
            } catch {
              return null;
            }
          })();

          if (me) {
            const roles = Array.isArray((me as any)?.shop_roles)
              ? (me as any).shop_roles
              : [];
            if (roles.length > 0 && roles[0]?.shop_code) {
              router.replace(`/shops/${roles[0].shop_code}/dashboard`);
              return;
            }
          }

          router.replace(safeReturnTo(returnTo));
          return;
        }

        // normal init
        const { accessToken } = TokenStorage.load();
        if (accessToken) {
          await fetchMe();
        }
      } finally {
        setIsLoading(false);
        setAuthReady(true);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    searchParams,
    router,
    clientId,
    redirectUri,
    endpoints.token,
    audience,
    fetchMe,
  ]);

  const login = async (_args: { email: string; password: string }) => {
    if (!auth0Domain || !clientId) {
      throw new Error(
        "Auth0 env missing: NEXT_PUBLIC_AUTH0_DOMAIN / NEXT_PUBLIC_AUTH0_CLIENT_ID",
      );
    }
    if (!audience) {
      throw new Error("Auth0 env missing: NEXT_PUBLIC_AUTH0_AUDIENCE");
    }

    const verifier = randomString(64);
    const challenge = await sha256Base64Url(verifier);

    try {
      sessionStorage.setItem(PKCE_VERIFIER_KEY, verifier);

      const s = randomString(32);
      sessionStorage.setItem(OIDC_STATE_KEY, s);

      const currentPath =
        typeof window !== "undefined"
          ? `${window.location.pathname}${window.location.search}`
          : "/";
      const returnTo = currentPath.startsWith("/login") ? "/" : currentPath;
      sessionStorage.setItem(OIDC_RETURN_TO_KEY, returnTo);

      const params = new URLSearchParams();
      params.set("response_type", "code");
      params.set("client_id", clientId);
      params.set("redirect_uri", redirectUri);
      params.set("scope", scopes);
      params.set("audience", audience);
      params.set("code_challenge", challenge);
      params.set("code_challenge_method", "S256");
      params.set("state", s);

      window.location.assign(`${endpoints.authorize}?${params.toString()}`);
    } catch {
      TokenStorage.clear();
      clearOidcSessionState();
      throw new Error("auth0_login_start_failed");
    }
  };

  const logout = async () => {
    TokenStorage.clear();
    clearOidcSessionState();
    setUser(null);

    if (!auth0Domain || !clientId) {
      router.replace("/login");
      return;
    }

    const params = new URLSearchParams();
    // Auth0 logout: returnTo は Allowed Logout URLs に登録が必要
    params.set("client_id", clientId);
    params.set("returnTo", postLogoutRedirectUri);

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
      refresh,
    }),
    [isLoading, authReady, user, apiClient, refresh],
  );

  return <AuthCtx.Provider value={value}>{children}</AuthCtx.Provider>;
}
