import type { AuthAdapter } from "@/ui/auth/contracts";
import type { AuthUser } from "@/domain/auth/AuthUser";
import { createSanctumApiClient } from "./SanctumApiClient";
import { getXsrfToken } from "./xsrf";
export class SanctumAuthAdapter implements AuthAdapter {
  // ✅ /api/* 専用クライアント
  private api = createSanctumApiClient();

  /**
   * 認証済みかどうかの確認
   * /api/me を叩く（認証済み前提）
   */
  async init(): Promise<AuthUser | null> {
    try {
      return await this.api.get<AuthUser>("/me");
    } catch (e: any) {
      if (e.status === 401) return null;
      throw e;
    }
  }

  /**
   * ログイン（web 認証）
   * ※ ApiClient は使わない
   */
  async login(payload: { email: string; password: string }) {
    // ① CSRF Cookie 発行
    await fetch("/sanctum/csrf-cookie", {
      credentials: "include",
    });

    // ② XSRF-TOKEN を Cookie から取得
    const xsrfToken = getXsrfToken();
    if (!xsrfToken) {
      throw new Error("XSRF token not found");
    }

    // ③ login
    const res = await fetch("/login", {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-XSRF-TOKEN": xsrfToken, // ← ★ これが足りなかった
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      const text = await res.text().catch(() => "");
      throw new Error(text || "Login failed");
    }
  }

  /**
   * ログアウト（web 認証）
   * ※ ApiClient は使わない
   */
  async logout() {
    const xsrfToken = getXsrfToken();

    await fetch("/logout", {
      method: "POST",
      credentials: "include",
      headers: {
        Accept: "application/json",
        ...(xsrfToken ? { "X-XSRF-TOKEN": xsrfToken } : {}),
      },
    });
  }

  /**
   * 認証後 API 用クライアント
   */
  getApiClient() {
    return this.api;
  }
}
