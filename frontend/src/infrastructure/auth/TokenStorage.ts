import type { AuthTokens } from "@/domain/auth/AuthTokens";

const ACCESS = "access_token";
const REFRESH = "refresh_token";

export const TokenStorage = {
  save(tokens: AuthTokens) {
    localStorage.setItem(ACCESS, tokens.accessToken);
    localStorage.setItem(REFRESH, tokens.refreshToken);
  },

  load(): AuthTokens {
    return {
      accessToken: localStorage.getItem(ACCESS) ?? "",
      refreshToken: localStorage.getItem(REFRESH) ?? "",
    };
  },

  clear() {
    localStorage.removeItem(ACCESS);
    localStorage.removeItem(REFRESH);
  },
};
