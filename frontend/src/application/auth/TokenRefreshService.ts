import type { AuthTokens } from "@/domain/auth/AuthTokens";
import type { LaravelAuthApi } from "@/infrastructure/auth/LaravelAuthApi";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";
import { getDeviceId } from "@/utils/deviceId";

export class TokenRefreshService {
  private refreshing: Promise<AuthTokens> | null = null;
  private refreshFailed = false;

  constructor(private api: LaravelAuthApi) {}

  async refresh(): Promise<AuthTokens> {
    if (this.refreshFailed) {
      throw new Error("refresh_already_failed");
    }

    if (this.refreshing) {
      return this.refreshing;
    }

    const tokens = TokenStorage.load();
    if (!tokens.refreshToken) {
      this.refreshFailed = true;
      throw new Error("no_refresh_token");
    }

    this.refreshing = (async () => {
      try {
        const newTokens = await this.api.refresh(
          tokens.refreshToken,
          getDeviceId(),
        );

        TokenStorage.save(newTokens);
        return newTokens;
      } catch (e) {
        this.refreshFailed = true;
        TokenStorage.clear();
        throw e;
      } finally {
        this.refreshing = null;
      }
    })();

    return this.refreshing;
  }

  reset() {
    this.refreshFailed = false;
  }
}
