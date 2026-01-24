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
  const apiBase = `${base.replace(/\/+$/, "")}/api`;

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
      credentials: "omit",
      cache: "no-store",
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

/* =========================================================
   Keys
========================================================= */
const PKCE_VERIFIER_KEY = "auth0_pkce_verifier_v1";
const OIDC_STATE_KEY = "auth0_state_v1";
const OIDC_RETURN_TO_KEY = "auth0_return_to_v1";
const EXCHANGE_LOCK_KEY = "auth0_exchange_lock_v1";

const NAV_LOCK_KEY = "occore_nav_lock_v1";
const OWNER_REDIRECT_KEY = "occore_owner_shop_code_v1";
const JUST_LOGGED_IN_KEY = "occore_just_logged_in_v1";

// ✅ StrictMode 二重実行を完全に潰す “グローバルロック”
const GLOBAL_LOCK_KEY = "__occore_auth0_exchange_lock_v1";

function acquireGlobalLock(): boolean {
  const g = globalThis as any;
  if (g[GLOBAL_LOCK_KEY]) return false;
  g[GLOBAL_LOCK_KEY] = true;
  return true;
}
function releaseGlobalLock(): void {
  const g = globalThis as any;
  g[GLOBAL_LOCK_KEY] = false;
}

function safeReturnTo(raw: string | null | undefined): string {
  if (!raw) return "/";
  if (raw.startsWith("/")) return raw;
  return "/";
}

/* =========================================================
   Storage helpers
   - PKCE (state/verifier) は localStorage
   - returnTo / locks / app flags は sessionStorage
========================================================= */
function getSessionItem(key: string): string | null {
  try {
    return sessionStorage.getItem(key);
  } catch {
    return null;
  }
}
function setSessionItem(key: string, value: string): void {
  try {
    sessionStorage.setItem(key, value);
  } catch {
    // ignore
  }
}
function removeSessionItem(key: string): void {
  try {
    sessionStorage.removeItem(key);
  } catch {
    // ignore
  }
}

function getPkceItem(key: string): string | null {
  try {
    return localStorage.getItem(key);
  } catch {
    return null;
  }
}
function setPkceItem(key: string, value: string): void {
  try {
    localStorage.setItem(key, value);
  } catch {
    // ignore
  }
}
function removePkceItem(key: string): void {
  try {
    localStorage.removeItem(key);
  } catch {
    // ignore
  }
}

function clearOidcSessionState() {
  // PKCE（localStorage）
  removePkceItem(PKCE_VERIFIER_KEY);
  removePkceItem(OIDC_STATE_KEY);

  // sessionStorage 側
  removeSessionItem(OIDC_RETURN_TO_KEY);
  removeSessionItem(EXCHANGE_LOCK_KEY);
}

function navOnce(router: ReturnType<typeof useRouter>, to: string) {
  try {
    if (sessionStorage.getItem(NAV_LOCK_KEY) === "1") return;
    sessionStorage.setItem(NAV_LOCK_KEY, "1");
  } catch {
    // ignore
  }
  router.replace(to);
}

function clearNavLockSoon() {
  setTimeout(() => removeSessionItem(NAV_LOCK_KEY), 0);
}

function clearJustLoggedInSoon() {
  setTimeout(() => removeSessionItem(JUST_LOGGED_IN_KEY), 1500);
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

function normalizeScopes(scopes: string): string {
  return scopes.trim().replace(/\.$/, "").replace(/\s+/g, " ");
}

function pickOwnerShopCode(me: any): string | null {
  const roles = Array.isArray(me?.shop_roles) ? me.shop_roles : [];
  if (!roles.length) return null;
  const r0 = roles[0];
  if (r0?.role === "owner" && r0?.shop_code) return r0.shop_code;
  if (r0?.shop_code) return r0.shop_code;
  return null;
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

  const scopes = normalizeScopes(
    process.env.NEXT_PUBLIC_OIDC_SCOPES ?? "openid profile email",
  );

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
    } catch (e: any) {
      if (e?.status === 401) TokenStorage.clear();
      setUser(null);
      return null;
    }
  }, [apiClient]);

  const refresh = useCallback(async () => {
    await fetchMe();
  }, [fetchMe]);

  useEffect(() => {
    (async () => {
      try {
        const code = searchParams.get("code");
        const state = searchParams.get("state");
        const error = searchParams.get("error");
        const errorDescription = searchParams.get("error_description");

        // Auth0側エラー
        if (error) {
          TokenStorage.clear();
          clearOidcSessionState();
          releaseGlobalLock();
          router.replace(
            `/login?oidc_error=${encodeURIComponent(error)}${
              errorDescription
                ? `&oidc_error_description=${encodeURIComponent(errorDescription)}`
                : ""
            }`,
          );
          return;
        }

        // ===== callback =====
        if (code) {
          // ✅ まず global lock（StrictMode 二重実行をここで殺す）
          if (!acquireGlobalLock()) return;

          // ✅ session lock（同一tab内の二重実行も抑止）
          if (getSessionItem(EXCHANGE_LOCK_KEY) === "1") return;
          setSessionItem(EXCHANGE_LOCK_KEY, "1");

          if (exchangeInFlight.current) return;
          exchangeInFlight.current = true;

          if (!auth0Domain || !clientId || !audience) {
            TokenStorage.clear();
            removeSessionItem(EXCHANGE_LOCK_KEY);
            releaseGlobalLock();
            router.replace("/login?oidc_error=env_missing");
            return;
          }

          // ✅ state/verifier は localStorage から読む
          const expectedState = getPkceItem(OIDC_STATE_KEY);

          if (!expectedState || state !== expectedState) {
            TokenStorage.clear();
            // 失敗時は lock 解除（再試行可能）
            removeSessionItem(EXCHANGE_LOCK_KEY);
            releaseGlobalLock();
            router.replace("/login?oidc_error=state_mismatch");
            return;
          }

          const verifier = getPkceItem(PKCE_VERIFIER_KEY);
          if (!verifier) {
            TokenStorage.clear();
            removeSessionItem(EXCHANGE_LOCK_KEY);
            releaseGlobalLock();
            router.replace("/login?oidc_error=missing_verifier");
            return;
          }

          const body = new URLSearchParams();
          body.set("grant_type", "authorization_code");
          body.set("client_id", clientId);
          body.set("code", code);
          body.set("redirect_uri", redirectUri);
          body.set("code_verifier", verifier);
          body.set("audience", audience);

          const res = await fetch(endpoints.token, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
              Accept: "application/json",
            },
            body: body.toString(),
            cache: "no-store",
          });

          if (!res.ok) {
            const text = await res.text().catch(() => "");
            TokenStorage.clear();
            removeSessionItem(EXCHANGE_LOCK_KEY);
            releaseGlobalLock();
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
            removeSessionItem(EXCHANGE_LOCK_KEY);
            releaseGlobalLock();
            router.replace("/login?oidc_error=missing_access_token");
            return;
          }

          TokenStorage.save({ accessToken, refreshToken });

          // ✅ 成功後に PKCE（localStorage）を消す
          removePkceItem(OIDC_STATE_KEY);
          removePkceItem(PKCE_VERIFIER_KEY);

          // ✅ session lock解除
          removeSessionItem(EXCHANGE_LOCK_KEY);
          releaseGlobalLock();

          const me = await fetchMe();
          const shopCode = pickOwnerShopCode(me as any);

          // owner は必ず dashboard
          if (shopCode) {
            setSessionItem(JUST_LOGGED_IN_KEY, "1");
            setSessionItem(OWNER_REDIRECT_KEY, shopCode);

            window.location.assign(`/shops/${shopCode}/dashboard`);
            return;
          }

          // owner以外はトップ
          navOnce(router, "/");
          clearNavLockSoon();
          clearJustLoggedInSoon();
          return;
        }

        // ===== normal init =====
        const { accessToken } = TokenStorage.load();
        if (accessToken) {
          const me = await fetchMe();
          console.log("[🔥IdaasProvider] after fetchMe", {
            shopCode: pickOwnerShopCode(me as any),
            origin: window.location.origin,
            href: window.location.href,
          });
        }
      } finally {
        setIsLoading(false);
        setAuthReady(true);
      }
    })();
  }, [
    searchParams,
    router,
    auth0Domain,
    clientId,
    audience,
    redirectUri,
    endpoints.token,
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
      // ログイン開始：古いロック/フラグを掃除
      removeSessionItem(EXCHANGE_LOCK_KEY);
      releaseGlobalLock();
      removeSessionItem(NAV_LOCK_KEY);
      removeSessionItem(OWNER_REDIRECT_KEY);
      removeSessionItem(JUST_LOGGED_IN_KEY);

      // ✅ PKCE (localStorage)
      setPkceItem(PKCE_VERIFIER_KEY, verifier);

      const s = randomString(32);
      setPkceItem(OIDC_STATE_KEY, s);

      // returnTo は sessionStorage のまま
      const currentPath =
        typeof window !== "undefined"
          ? `${window.location.pathname}${window.location.search}`
          : "/";
      const returnTo = currentPath.startsWith("/login") ? "/" : currentPath;
      setSessionItem(OIDC_RETURN_TO_KEY, returnTo);

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
      releaseGlobalLock();
      throw new Error("auth0_login_start_failed");
    }
  };

  const logout = async () => {
    TokenStorage.clear();
    clearOidcSessionState();
    releaseGlobalLock();
    setUser(null);

    removeSessionItem(NAV_LOCK_KEY);
    removeSessionItem(OWNER_REDIRECT_KEY);
    removeSessionItem(JUST_LOGGED_IN_KEY);

    if (!auth0Domain || !clientId) {
      router.replace("/login");
      return;
    }

    const params = new URLSearchParams();
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
