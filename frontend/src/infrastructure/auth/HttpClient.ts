import axios, { AxiosInstance, AxiosError } from "axios";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";
import type { TokenRefreshService } from "@/application/auth/TokenRefreshService";

export function createHttpClient(
  refreshService: TokenRefreshService | null,
): AxiosInstance {
  const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL ?? ""; // 例: https://dxxxxx.cloudfront.net
  const baseURL = apiBase ? `${apiBase}/api` : "/api";

  const client = axios.create({
    baseURL,
    // withCredentials: false（JWT固定）
  });

  client.interceptors.request.use((config) => {
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

  client.interceptors.response.use(
    (res) => res,
    async (error: AxiosError) => {
      const status = error.response?.status;
      const original = error.config as any;

      if (original?.url?.includes("/auth/refresh"))
        return Promise.reject(error);
      if (!refreshService) return Promise.reject(error);

      if (status === 401 && !original?._retry) {
        original._retry = true;
        await refreshService.refresh();
        return client(original);
      }

      return Promise.reject(error);
    },
  );

  return client;
}
