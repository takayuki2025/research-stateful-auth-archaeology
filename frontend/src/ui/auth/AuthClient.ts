export type AuthUser = {
  id: number;
  email: string;
  email_verified_at?: string | null;
  profile_completed: boolean;
};

export type LoginResult = {
  user: AuthUser;
  isFirstLogin: boolean;
};

export type RegisterResult = {
  needsEmailVerification: boolean;
};

export interface AuthClient {
  login(email: string, password: string): Promise<LoginResult>;
  register(
    name: string,
    email: string,
    password: string
  ): Promise<RegisterResult>;
  logout(): Promise<void>;
  me(): Promise<AuthUser | null>;

  get<T = any>(url: string): Promise<T>;
  post<T = any>(url: string, body?: any): Promise<T>;
  patch<T = any>(url: string, body?: any): Promise<T>;
  delete<T = any>(url: string): Promise<T>;
}
