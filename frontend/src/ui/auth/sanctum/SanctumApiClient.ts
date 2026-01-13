import type { ApiClient } from "@/ui/auth/contracts";

/**
 * =========================
 * Options
 * =========================
 */
type RequestOptions = {
  /**
   * API prefix
   * - default: "/api"
   * - example: "/api"
   */
  basePrefix?: string;
};

/**
 * =========================
 * CSRF 管理（Sanctum SPA 用）
 * =========================
 */
let csrfInitialized = false;

async function ensureCsrfCookie(): Promise<void> {
  if (csrfInitialized) return;

  await fetch("/sanctum/csrf-cookie", {
    method: "GET",
    credentials: "include",
  });

  csrfInitialized = true;
}

/**
 * =========================
 * Sanctum API Client
 * =========================
 *
 * - Laravel Sanctum (stateful SPA) 専用
 * - JWT 非使用
 * - credentials: include 前提
 * - CSRF 自動初期化
 */
export function createSanctumApiClient(opts: RequestOptions = {}): ApiClient {
  const basePrefix = opts.basePrefix ?? "/api";

  /**
   * -------------------------
   * Core Request
   * -------------------------
   */
  const request = async <T>(
    method: "GET" | "POST" | "PATCH" | "DELETE",
    url: string,
    body?: unknown
  ): Promise<T> => {
    // =========================
    // CSRF（GET 以外は必須）
    // =========================
    if (method !== "GET") {
      await ensureCsrfCookie();
    }

    const fullUrl = url.startsWith("http")
      ? url
      : `${basePrefix}${url.startsWith("/") ? "" : "/"}${url}`;

    const isFormData = body instanceof FormData;

    const res = await fetch(fullUrl, {
      method,
      credentials: "include",
      headers: isFormData
        ? {
            Accept: "application/json",
          }
        : {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
      body:
        body === undefined
          ? undefined
          : isFormData
            ? body
            : JSON.stringify(body),
    });

    // =========================
    // Error Handling
    // =========================
    if (!res.ok) {
      let message = `Request failed: ${res.status} ${res.statusText}`;

      try {
        const contentType = res.headers.get("content-type") || "";
        if (contentType.includes("application/json")) {
          const json = await res.json();
          message = json?.message ?? message;
        } else {
          const text = await res.text();
          if (text) message = text;
        }
      } catch {
        // ignore parse error
      }

      const error: any = new Error(message);
      error.status = res.status;
      throw error;
    }

    // =========================
    // No Content
    // =========================
    if (res.status === 204) {
      return undefined as unknown as T;
    }

    // =========================
    // JSON Response
    // =========================
    const contentType = res.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
      const text = await res.text().catch(() => "");
      throw new Error(`Non-JSON response received: ${text.slice(0, 200)}`);
    }

    return res.json();
  };

  /**
   * =========================
   * ApiClient Interface
   * =========================
   */
  return {
    get: <T>(url: string) => request<T>("GET", url),
    post: <T>(url: string, body?: unknown) => request<T>("POST", url, body),
    patch: <T>(url: string, body?: unknown) => request<T>("PATCH", url, body),
    delete: <T>(url: string) => request<T>("DELETE", url),
  };
}
