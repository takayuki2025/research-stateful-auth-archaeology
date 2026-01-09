/* =========================
   API Client Contract
========================= */
export interface ApiClient {
  get<T>(url: string): Promise<T>;
  post<T>(url: string, body?: unknown): Promise<T>;
  patch<T>(url: string, body?: unknown): Promise<T>;
  delete<T>(url: string): Promise<T>;
}

/* =========================
   Auth User
========================= */
export type AuthUser = {
  id: number;
  email?: string;
  email_verified_at?: string | null;
  profile_completed?: boolean;
  display_name?: string | null;
};

/* =========================
   Auth Context
========================= */
export type AuthContext = {
  isLoading: boolean;
  authReady: boolean;
  isAuthenticated: boolean;
  user: AuthUser | null;
  apiClient: ApiClient;

  login(payload: { email: string; password: string }): Promise<void>;
  logout(): Promise<void>;
  refresh: () => Promise<void>;
};

/* =========================
   Auth Adapter
========================= */
export interface AuthAdapter {
  init(): Promise<AuthUser | null>;
  login(payload: { email: string; password: string }): Promise<void>;
  logout(): Promise<void>;
  getApiClient(): ApiClient;
}
