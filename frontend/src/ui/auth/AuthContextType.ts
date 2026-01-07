import type { AuthUser } from "@/types/auth";
import type { AxiosInstance } from "axios";

export type RegisterResult = {
  needsEmailVerification: boolean;
};

export type LoginResult = {
  user: AuthUser;
  isFirstLogin: boolean;
};

export interface AuthContextType {
  user: AuthUser | null;
  isAuthenticated: boolean;

  /** 初期化中（/me 取得・login 中など） */
  isLoading: boolean;

  /** AuthProvider の初期化が完了したか */
  isReady: boolean;

  login: (args: { email: string; password: string }) => Promise<LoginResult>;
  register: (args: {
    name: string;
    email: string;
    password: string;
  }) => Promise<RegisterResult>;
  logout: () => Promise<void>;
  reloadUser: () => Promise<void>;
  reloginWithFirebaseToken: (idToken: string) => Promise<void>;

  apiClient: AxiosInstance | null;
}
