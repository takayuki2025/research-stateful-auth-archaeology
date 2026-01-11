import type {
  AuthClient,
  LoginResult,
  RegisterResult,
} from "./AuthClient";

import type { AuthUser } from "@/domain/auth/AuthUser";

export type AxiosLikeClient = {
  get<T = any>(url: string): Promise<{ data: T }>;
  post<T = any>(url: string, body?: any): Promise<{ data: T }>;
  patch<T = any>(url: string, body?: any): Promise<{ data: T }>;
  delete<T = any>(url: string): Promise<{ data: T }>;
};

export interface AuthContextType {
  user: AuthUser | null;
  isAuthenticated: boolean;

  isLoading: boolean;
  isReady: boolean;

  authClient: AuthClient;
  apiClient: AxiosLikeClient;

  login(args: { email: string; password: string }): Promise<LoginResult>;
  register(args: {
    name: string;
    email: string;
    password: string;
  }): Promise<RegisterResult>;
  logout(): Promise<void>;
  reloadUser(): Promise<void>;
  reloginWithFirebaseToken(idToken: string): Promise<void>;
}
