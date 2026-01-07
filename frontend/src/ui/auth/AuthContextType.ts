import type {
  AuthClient,
  AuthUser,
  LoginResult,
  RegisterResult,
} from "./AuthClient";

export type AxiosLikeResponse<T> = { data: T };

export type AxiosLikeClient = {
  get<T = any>(url: string): Promise<AxiosLikeResponse<T>>;
  post<T = any>(url: string, body?: any): Promise<AxiosLikeResponse<T>>;
  patch<T = any>(url: string, body?: any): Promise<AxiosLikeResponse<T>>;
  delete<T = any>(url: string): Promise<AxiosLikeResponse<T>>;
};

export interface AuthContextType {
  user: AuthUser | null;
  isAuthenticated: boolean;

  /** 初期化中（/api/me 取得など） */
  isLoading: boolean;

  /** 初期化完了フラグ（UI側の分岐を安定させる） */
  isReady: boolean;

  /** 統一クライアント（UIはこれを使うのが正解） */
  authClient: AuthClient;

  /** 既存互換: axios っぽい .get().then(res => res.data) */
  apiClient: AxiosLikeClient;

  login: (args: { email: string; password: string }) => Promise<LoginResult>;
  register: (args: {
    name: string;
    email: string;
    password: string;
  }) => Promise<RegisterResult>;
  logout: () => Promise<void>;
  reloadUser: () => Promise<void>;

  /** 将来分岐用：Firebase token で再ログイン等（Statefulでは通常未使用） */
  reloginWithFirebaseToken: (idToken: string) => Promise<void>;
}
