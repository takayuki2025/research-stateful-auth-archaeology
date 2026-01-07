import type { AxiosInstance } from "axios";


export const createSWRFetcher =
  (apiClient: AxiosInstance | null) => async (url: string) => {
    if (!apiClient) {
      throw new Error("API client is not ready");
    }

    const res = await apiClient.get(url);
    return res.data;
  };
