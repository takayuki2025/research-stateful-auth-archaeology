import { apiClient } from "./apiClient";

export async function login(email: string, password: string) {
  // ① CSRF Cookie を取得
  await apiClient.get("/sanctum/csrf-cookie");

  // ② ログイン
  await apiClient.post("/login", {
    email,
    password,
  });

  // ③ 認証済ユーザー取得
  const me = await apiClient.get("/me");

  return me.data;
}

export async function logout() {
  await apiClient.post("/logout");
}

export async function fetchMe() {
  const res = await apiClient.get("/me");
  return res.data;
}
