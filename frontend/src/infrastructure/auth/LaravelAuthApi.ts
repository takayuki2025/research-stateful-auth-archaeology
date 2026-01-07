import type { AxiosInstance, AxiosError } from "axios";
import type { AuthTokens } from "@/domain/auth/AuthTokens";
import type { AuthUser } from "@/types/auth";

/**
 * Firebase login result
 */
export type LoginWithFirebaseResult = {
  tokens: AuthTokens;
  user: AuthUser;
  isFirstLogin: boolean;
};

export class LaravelAuthApi {
  private _client: AxiosInstance | null;

  constructor(client: AxiosInstance | null) {
    this._client = client;
  }

  /**
   * client を後から注入できるようにする（AuthProvider 初期化順問題の解決）
   */
  public setClient(client: AxiosInstance): void {
    this._client = client;
  }

  public get client(): AxiosInstance {
    if (!this._client) {
      throw new Error("LaravelAuthApi client is not initialized");
    }
    return this._client;
  }

  /**
   * Firebase ID Token → Laravel JWT
   */
  async loginWithFirebaseToken(
    firebaseToken: string,
    deviceId: string,
  ): Promise<LoginWithFirebaseResult> {
    const res = await this.client.post("/login_or_register", {
      firebase_token: firebaseToken,
      device_id: deviceId,
    });

    return {
      tokens: {
        accessToken: res.data.token,
        refreshToken: res.data.refreshToken,
      },
      user: res.data.user as AuthUser,
      isFirstLogin: res.data.isFirstLogin,
    };
  }

  /**
   * Refresh JWT
   *
   * ⚠️ 失敗時は「意味のある Error」を投げる
   * （axios interceptor / TokenRefreshService 側で制御する）
   */
  async refresh(refreshToken: string, deviceId: string): Promise<AuthTokens> {
    try {
      const res = await this.client.post("/auth/refresh", {
        refresh_token: refreshToken,
        device_id: deviceId,
      });

      return {
        accessToken: res.data.access_token,
        refreshToken: res.data.refresh_token,
      };
    } catch (err) {
      const error = err as AxiosError<any>;

      // refresh token 無効・期限切れ
      if (error.response?.status === 401 || error.response?.status === 403) {
        throw new Error("refresh_token_invalid");
      }

      // その他（500 / network / unexpected）
      throw new Error("refresh_failed");
    }
  }

  /**
   * Get current authenticated user
   */
  async me(): Promise<AuthUser> {
    const res = await this.client.get("/me");
    return res.data as AuthUser;
  }

  /**
   * Logout (server-side)
   */
  async logout(): Promise<void> {
    await this.client.post("/logout");
  }
}
