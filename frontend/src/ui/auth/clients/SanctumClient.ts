import { AuthClient } from "../AuthClient";

/**
 * 共通 JSON ハンドラ
 */
const json = async (res: Response) => {
  if (!res.ok) {
    const data = await res.json().catch(() => ({}));
    throw new Error(data?.message ?? "Request failed");
  }
  return res.json();
};

/**
 * Sanctum 用 AuthClient 実装
 * - SPA + Cookie + Session 前提
 * - Occ_Auth_v1 の「Sanctum 分岐」
 */
export const SanctumClient: AuthClient = {
  /* =========================
     🔐 Auth
  ========================= */

  async login(email: string, password: string) {
    // CSRF Cookie
    await fetch("/sanctum/csrf-cookie", {
      credentials: "include",
    });

    const res = await fetch("/login", {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      throw new Error(data?.message ?? "Login failed");
    }

    // { user }
    return res.json();
  },

  async logout() {
    await fetch("/logout", {
      method: "POST",
      credentials: "include",
      headers: {
        Accept: "application/json",
      },
    });
  },

  async me() {
    const res = await fetch("/api/me", {
      method: "GET",
      credentials: "include",
      headers: {
        Accept: "application/json",
      },
    });

    if (!res.ok) return null;
    return res.json();
  },

  /**
   * Sanctum モードでは register は使わない
   * （Firebase / Auth0 分岐用に interface 上は保持）
   */
  async register() {
    throw new Error("Register is not supported in Sanctum mode");
  },

  /* =========================
     📡 Generic API
  ========================= */

  async get(url: string) {
    const res = await fetch(url, {
      method: "GET",
      credentials: "include",
      headers: {
        Accept: "application/json",
      },
    });
    return json(res);
  },

  async post(url: string, body?: any) {
    const res = await fetch(url, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    return json(res);
  },

  async patch(url: string, body?: any) {
    const res = await fetch(url, {
      method: "PATCH",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    return json(res);
  },

  async delete(url: string) {
    const res = await fetch(url, {
      method: "DELETE",
      credentials: "include",
      headers: {
        Accept: "application/json",
      },
    });
    return json(res);
  },
};
