"use client";

import React, {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
  useMemo,
  type ReactNode,
} from "react";

import axios, { AxiosInstance } from "axios";
import {
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  updateProfile,
  type User as FirebaseUser,
  onIdTokenChanged,
} from "firebase/auth";

import { getFirebaseAuth } from "@/lib/firebase";

/* ============================================================
   型定義
============================================================ */
export interface UserRole {
  id: number;
  name: string;
  slug: string;
  shop_id: number | null;
}

export interface LaravelUser {
  id: number;
  name: string;
  email: string;
  // ★ ここを「単体の role」ではなく「roles 配列」に変更
  roles: UserRole[];

  // すでにあればそのまま
  user_image?: string | null;
  email_verified_at?: string | null;
  emailVerified: boolean;
}

interface LoginResponse {
  token: string;
  user: LaravelUser;
  status: "login" | "register";
  needsEmailVerification: boolean;
}

export interface AuthContextType {
  user: LaravelUser | null;
  firebaseUser: FirebaseUser | null;

  token: string | null;
  apiClient: AxiosInstance | null;

  isAuthenticated: boolean;
  isLoading: boolean;

  login: (params: { email: string; password: string }) => Promise<void>;
  register: (params: {
    name: string;
    email: string;
    password: string;
  }) => Promise<{ needsEmailVerification: boolean }>;

  reloadAuthToken: () => Promise<void>;
  logout: () => Promise<void>;
}

/* ============================================================
   Context
============================================================ */
const AuthContext = createContext<AuthContextType | null>(null);

/* ============================================================
   Helper
============================================================ */
function loginWithLaravel(idToken: string, name?: string) {
  return axios
    .post<LoginResponse>(
      "/api/login_or_register",
      { id_token: idToken, name },
      { withCredentials: true },
    )
    .then((r) => r.data);
}

function createSanctumApiClient(token: string): AxiosInstance {
  return axios.create({
    baseURL: "/api",
    withCredentials: true,
    headers: {
      Authorization: `Bearer ${token}`,
    },
  });
}

/* ============================================================
   AuthProvider（SSR-safe）
============================================================ */
export function AuthProvider({ children }: { children: ReactNode }) {
  const [firebaseUser, setFirebaseUser] = useState<FirebaseUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<LaravelUser | null>(null);

  const [auth, setAuth] = useState<any>(null); // Firebase Auth インスタンス
  const [isLoading, setIsLoading] = useState(true);

  const [isRegistering, setIsRegistering] = useState(false);

  /* ============================================================
     ★ Point 1：Firebase 初期化は SSR では行わない
============================================================ */
  useEffect(() => {
    const _auth = getFirebaseAuth(); // ← window がある環境のみで動く
    setAuth(_auth);
  }, []);

  /* ============================================================
     ★ Point 2：localStorage の永続化は client でのみ復元
============================================================ */
  useEffect(() => {
    if (typeof window === "undefined") return;

    const savedToken = localStorage.getItem("token");
    const savedUser = localStorage.getItem("user");

    if (savedToken) setToken(savedToken);
    if (savedUser) setUser(JSON.parse(savedUser));

    setIsLoading(false);
  }, []);

  /* ============================================================
     ★ Point 3：Firebase Auth State Listener（client only）
============================================================ */
  useEffect(() => {
    if (!auth) return;

    const unsub = onIdTokenChanged(auth, async (u) => {
      setFirebaseUser(u);

      if (isRegistering) return;

      if (u && !token) {
        const idToken = await u.getIdToken(true);
        const result = await loginWithLaravel(idToken);

        setToken(result.token);
        setUser(result.user);

        localStorage.setItem("token", result.token);
        localStorage.setItem("user", JSON.stringify(result.user));
      }
    });

    return () => unsub();
  }, [auth, token, isRegistering]);

  /* ============================================================
     Axios Client
============================================================ */
  const apiClient = useMemo(() => {
    if (!token) return null;

    const instance = createSanctumApiClient(token);

    instance.interceptors.response.use(
      (res) => res,
      async (error) => {
        const original = error.config;

        if (
          error.response?.status === 401 &&
          !original._retry &&
          firebaseUser
        ) {
          original._retry = true;

          const newIdToken = await firebaseUser.getIdToken(true);
          const result = await loginWithLaravel(newIdToken);

          setToken(result.token);
          setUser(result.user);

          localStorage.setItem("token", result.token);
          localStorage.setItem("user", JSON.stringify(result.user));

          original.headers.Authorization = `Bearer ${result.token}`;

          return instance(original);
        }

        return Promise.reject(error);
      },
    );

    return instance;
  }, [token, firebaseUser]);

  /* ============================================================
     LOGIN
============================================================ */
  const login = useCallback(
    async ({ email, password }: { email: string; password: string }) => {
      if (!auth) throw new Error("Auth not initialized");

      const cred = await signInWithEmailAndPassword(auth, email, password);
      const idToken = await cred.user.getIdToken(true);

      const result = await loginWithLaravel(idToken);

      setToken(result.token);
      setUser(result.user);

      localStorage.setItem("token", result.token);
      localStorage.setItem("user", JSON.stringify(result.user));
    },
    [auth],
  );

  /* ============================================================
     REGISTER
============================================================ */
  const register = useCallback(
    async (params: { name: string; email: string; password: string }) => {
      if (!auth) throw new Error("Auth not initialized");

      setIsRegistering(true);
      try {
        const cred = await createUserWithEmailAndPassword(
          auth,
          params.email,
          params.password,
        );
        await updateProfile(cred.user, { displayName: params.name });

        const idToken = await cred.user.getIdToken(true);
        const result = await loginWithLaravel(idToken, params.name);

        setToken(result.token);
        setUser(result.user);

        localStorage.setItem("token", result.token);
        localStorage.setItem("user", JSON.stringify(result.user));

        return { needsEmailVerification: result.needsEmailVerification };
      } finally {
        setIsRegistering(false);
      }
    },
    [auth],
  );

  /* ============================================================
     LOGOUT
============================================================ */
  const logout = useCallback(async () => {
    if (!auth) return;
    await auth.signOut();

    setFirebaseUser(null);
    setUser(null);
    setToken(null);

    localStorage.removeItem("token");
    localStorage.removeItem("user");
  }, [auth]);

  /* ============================================================
     EXPORT VALUE
============================================================ */
  const value: AuthContextType = {
    user,
    firebaseUser,
    token,
    apiClient,

    isAuthenticated: !!token,
    isLoading,

    login,
    register,
    logout,

    reloadAuthToken: async () => {
      if (!firebaseUser) return;
      const idToken = await firebaseUser.getIdToken(true);
      const result = await loginWithLaravel(idToken);

      setToken(result.token);
      setUser(result.user);

      localStorage.setItem("token", result.token);
      localStorage.setItem("user", JSON.stringify(result.user));
    },
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

/* ============================================================
   Hooks
============================================================ */
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used inside AuthProvider");
  return ctx;
}

export function useApiClient() {
  const { apiClient } = useAuth();
  if (!apiClient) throw new Error("API client is not ready");
  return apiClient;
}
