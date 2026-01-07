"use client";

import { useApiClient } from "../hooks/useSanctumAuth";
import { AxiosError } from "axios";

export function useApi() {
  const api = useApiClient();

  const get = async <T>(url: string, params?: any): Promise<T> => {
    try {
      const res = await api.get(url, { params });
      return res.data;
    } catch (err) {
      handleError(err);
      throw err;
    }
  };

  const post = async <T>(url: string, data?: any): Promise<T> => {
    try {
      const res = await api.post(url, data);
      return res.data;
    } catch (err) {
      handleError(err);
      throw err;
    }
  };

  const put = async <T>(url: string, data?: any): Promise<T> => {
    try {
      const res = await api.put(url, data);
      return res.data;
    } catch (err) {
      handleError(err);
      throw err;
    }
  };

  const del = async <T>(url: string): Promise<T> => {
    try {
      const res = await api.delete(url);
      return res.data;
    } catch (err) {
      handleError(err);
      throw err;
    }
  };

  return { get, post, put, del };
}

function handleError(err: unknown) {
  if (err instanceof AxiosError) {
    console.error("API ERROR:", err.response?.status, err.response?.data);
  } else {
    console.error("Unknown API Error:", err);
  }
}
