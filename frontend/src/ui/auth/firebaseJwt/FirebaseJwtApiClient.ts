import type { ApiClient } from "@/ui/auth/contracts";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";

export function createFirebaseJwtApiClient(): ApiClient {
  const request = async <T>(
    method: string,
    url: string,
    body?: unknown
  ): Promise<T> => {
    const fullUrl = url.startsWith("http")
      ? url
      : `/api${url.startsWith("/") ? "" : "/"}${url}`;
    const { accessToken } = TokenStorage.load();

    const isFormData = body instanceof FormData;

    const res = await fetch(fullUrl, {
      method,
      credentials: "include", // 必要なら。JWTだけなら不要だが、混在期は付けても良い
      headers: {
        Accept: "application/json",
        ...(isFormData ? {} : { "Content-Type": "application/json" }),
        ...(accessToken ? { Authorization: `Bearer ${accessToken}` } : {}),
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
      const err: any = new Error(text || `Request failed: ${res.status}`);
      err.status = res.status;
      throw err;
    }
    if (res.status === 204) return undefined as unknown as T;
    return res.json();
  };

  return {
    get: (url) => request("GET", url),
    post: (url, body) => request("POST", url, body),
    patch: (url, body) => request("PATCH", url, body),
    delete: (url) => request("DELETE", url),
  };
}
