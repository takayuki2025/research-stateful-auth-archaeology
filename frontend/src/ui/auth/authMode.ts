export type AuthMode = "sanctum" | "jwt" | "auth0";

export function getAuthMode(): AuthMode {
  const v = process.env.NEXT_PUBLIC_AUTH_MODE;
  if (v === "jwt" || v === "auth0" || v === "sanctum") return v;
  return "sanctum";
}
