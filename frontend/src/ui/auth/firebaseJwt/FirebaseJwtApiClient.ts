import type { ApiClient } from "@/ui/auth/contracts";
import { createBearerApiClient } from "@/ui/auth/common/BearerApiClient";

export function createFirebaseJwtApiClient(): ApiClient {
  // BearerApiClient が TokenStorage から accessToken を読む
  return createBearerApiClient();
}
