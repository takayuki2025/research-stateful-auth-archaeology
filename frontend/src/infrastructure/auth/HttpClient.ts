import axios, { AxiosInstance, AxiosError } from "axios";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";
import type { TokenRefreshService } from "@/application/auth/TokenRefreshService";

export function createHttpClient(
  refreshService: TokenRefreshService | null,
): AxiosInstance {
  const client = axios.create({
    baseURL: "/api",
    // withCredentials: true,
  });

  /* ======================
     Request
  ====================== */
  client.interceptors.request.use((config) => {
    // refresh API は常に素通し
    if (config.url?.includes("/auth/refresh")) {
      if (config.headers) delete (config.headers as any).Authorization;
      return config;
    }

    const { accessToken } = TokenStorage.load();

    if (accessToken && accessToken.trim() !== "") {
      config.headers = config.headers ?? {};
      (config.headers as any).Authorization = `Bearer ${accessToken}`;
    } else {
      if (config.headers) delete (config.headers as any).Authorization;
    }

    return config;
  });

  /* ======================
     Response
  ====================== */
  client.interceptors.response.use(
    (res) => res,
    async (error: AxiosError) => {
      const status = error.response?.status;
      const original = error.config as any;

      // refresh 自体が失敗したらそのまま返す
      if (original?.url?.includes("/auth/refresh")) {
        return Promise.reject(error);
      }

      // ✅ refreshService が無いフェーズでは 401 を自動回復しない
      if (!refreshService) {
        return Promise.reject(error);
      }

      // ✅ refresh 有効フェーズのみ、ここが動く
      if (status === 401 && !original?._retry) {
        original._retry = true;

        try {
          await refreshService.refresh();
          return client(original);
        } catch (e) {
          // ここで TokenStorage.clear() は “用途次第”
          // refresh を導入したフェーズでのみ「ログアウト扱い」にしたいなら clear する
          // 今は refreshService が null なので到達しない
          return Promise.reject(e);
        }
      }

      return Promise.reject(error);
    },
  );

  return client;
}
