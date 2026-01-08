import type { ApiClient } from "@/ui/auth/contracts";

type RequestOptions = {
  basePrefix?: string; // default "/api"
};

export function createSanctumApiClient(opts: RequestOptions = {}): ApiClient {
  const basePrefix = opts.basePrefix ?? "/api";

  const request = async <T>(
    method: string,
    url: string,
    body?: unknown
  ): Promise<T> => {
    const fullUrl = url.startsWith("http")
      ? url
      : `${basePrefix}${url.startsWith("/") ? "" : "/"}${url}`;

    const res = await fetch(fullUrl, {
      method,
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    // 204 のように body が無い可能性もあるので安全に処理
    if (!res.ok) {
      const text = await res.text().catch(() => "");
      const err: any = new Error(
        text || `Request failed: ${res.status} ${res.statusText}`
      );
      err.status = res.status;
      throw err;
    }

    if (res.status === 204) return undefined as unknown as T;

    const contentType = res.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
      // JSON 以外が返るのはルーティング誤り（NextのHTMLなど）の可能性が高い
      const text = await res.text().catch(() => "");
      throw new Error(`Non-JSON response: ${text.slice(0, 200)}`);
    }

    return res.json();
  };

  return {
    get: (url) => request("GET", url),
    post: (url, body) => request("POST", url, body),
    patch: (url, body) => request("PATCH", url, body),
    delete: (url) => request("DELETE", url),
  };
}
