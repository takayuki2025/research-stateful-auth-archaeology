import { TokenStorage } from "@/infrastructure/auth/TokenStorage";

export type ApiClient = {
  get<T>(url: string): Promise<T>;
  post<T>(url: string, body?: unknown): Promise<T>;
  patch<T>(url: string, body?: unknown): Promise<T>;
  delete<T>(url: string): Promise<T>;
};

type CreateBearerApiClientOptions = {
  baseUrl?: string; // default: NEXT_PUBLIC_API_BASE_URL or http://localhost
  getAccessToken?: () => string | null;
  accept?: string; // default: application/json
};

export function createBearerApiClient(
  opts: CreateBearerApiClientOptions = {},
): ApiClient {
  const base =
    opts.baseUrl ?? process.env.NEXT_PUBLIC_API_BASE_URL ?? "http://localhost";
  const apiBase = `${base.replace(/\/+$/, "")}/api`;

  const getAccessToken =
    opts.getAccessToken ??
    (() => {
      const { accessToken } = TokenStorage.load();
      return accessToken || null;
    });

  const accept = opts.accept ?? "application/json";

  const request = async <T>(
    method: "GET" | "POST" | "PATCH" | "DELETE",
    url: string,
    body?: unknown,
  ): Promise<T> => {
    const fullUrl = url.startsWith("http")
      ? url
      : `${apiBase}${url.startsWith("/") ? "" : "/"}${url}`;

    const token = getAccessToken();

    const headers: Record<string, string> = { Accept: accept };
    if (token) headers.Authorization = `Bearer ${token}`;

    const isFormData =
      typeof FormData !== "undefined" && body instanceof FormData;

    // ✅ FormData 以外のときだけ JSON Header
    if (method !== "GET" && !isFormData) {
      headers["Content-Type"] = "application/json";
    }

    const res = await fetch(fullUrl, {
      method,
      headers,
      body:
        method === "GET"
          ? undefined
          : body === undefined
            ? undefined
            : isFormData
              ? (body as FormData)
              : JSON.stringify(body),
      cache: "no-store",
      credentials: "omit", // ✅ JWT/Bearer統一：Cookie送らない
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

    // ✅ JSON想定だが、万一 text/html が混じることがある（ALB直叩き等）
    const ct = res.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const t = await res.text().catch(() => "");
      const e: any = new Error(`Non-JSON response: ${t.slice(0, 200)}`);
      e.status = 500;
      throw e;
    }

    return (await res.json()) as T;
  };

  return {
    get: <T>(url: string) => request<T>("GET", url),
    post: <T>(url: string, body?: unknown) => request<T>("POST", url, body),
    patch: <T>(url: string, body?: unknown) => request<T>("PATCH", url, body),
    delete: <T>(url: string) => request<T>("DELETE", url),
  };
}
