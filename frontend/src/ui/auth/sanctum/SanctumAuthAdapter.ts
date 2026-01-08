import type { AuthAdapter, AuthUser } from "@/ui/auth/contracts";
import { createSanctumApiClient } from "./SanctumApiClient";

export class SanctumAuthAdapter implements AuthAdapter {
  private api = createSanctumApiClient();

  async init(): Promise<AuthUser | null> {
    try {
      return await this.api.get<AuthUser>("/me");
    } catch (e: any) {
      if (e.status === 401) return null;
      throw e;
    }
  }

  async login(payload: { email: string; password: string }) {
    await this.api.post("/login", payload);
  }

  async logout() {
    await this.api.post("/logout");
  }

  getApiClient() {
    return this.api;
  }
}
