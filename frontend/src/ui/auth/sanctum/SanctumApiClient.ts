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

    if (!res.ok) {
      const text = await res.text().catch(() => "");
      const err: any = new Error(
        text || `Request failed: ${res.status} ${res.statusText}`
      );
      err.status = res.status;
      throw err;
    }

    if (res.status === 204) {
      return undefined as unknown as T;
    }

    const contentType = res.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
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
