import type { AuthAdapter } from "./contracts";
import { getAuthMode } from "./authMode";
import { SanctumAuthAdapter } from "./sanctum/SanctumAuthAdapter";
// import { JwtAuthAdapter } from "./jwt/JwtAuthAdapter";
// import { Auth0Adapter } from "./auth0/Auth0Adapter";

export function createAuthAdapter(): AuthAdapter {
  const mode = getAuthMode();
  if (mode === "sanctum") return new SanctumAuthAdapter();
  // if (mode === "jwt") return new JwtAuthAdapter();
  // if (mode === "auth0") return new Auth0Adapter();
  return new SanctumAuthAdapter();
}
