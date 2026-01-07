import { useAuth } from "~/composables/useAuth";
import {
  useRuntimeConfig,
  navigateTo,
  useCookie,
  defineNuxtPlugin,
} from "#app";

export default defineNuxtPlugin((nuxtApp) => {
  // 環境変数からAPIのベースURLを取得。
  const apiBaseUrl = useRuntimeConfig().public.apiBaseUrl;

  const customFetch = $fetch.create({
    baseURL: apiBaseUrl,
    credentials: "include", // 認証情報のCookieを確実に含める

    // --- 1. リクエストインターセプター: X-XSRF-TOKEN と Bearer Token の付与 ---
    onRequest({ options }) {
      options.headers = options.headers || new Headers();
      const headers = options.headers as Headers;

      // 1-1. Acceptヘッダーの設定
      if (!headers.get("Accept")) {
        headers.set("Accept", "application/json");
      }

      // 1-2. CSRFトークンの付与 (Sanctum)
      if (process.client) {
        const xsrfCookie = useCookie("XSRF-TOKEN");
        const tokenValue = xsrfCookie.value;

        if (tokenValue) {
          // XSRF-TOKEN Cookieの値を、X-XSRF-TOKEN ヘッダーとして送信
          headers.set("X-XSRF-TOKEN", tokenValue);
          // console.log(`[CSRF] X-XSRF-TOKEN set: ${tokenValue.substring(0, 10)}...`);
        } else {
          // console.warn("[CSRF] XSRF-TOKEN cookie not found.");
        }
      }

      // 1-3. Bearerトークンの付与 (JWT/Token認証 - Piniaストア/useAuthに依存)
      const { token: localToken } = useAuth();
      if (localToken.value && !headers.get("Authorization")) {
        // トークンが存在し、かつ Authorization ヘッダーが未設定の場合に付与
        headers.set("Authorization", `Bearer ${localToken.value}`);
        // console.log(`[Auth] Bearer Token set in $api: ${localToken.value.substring(0, 10)}...`);
      }
    },

    // --- 2. レスポンスエラーインターセプター: 401 Unauthorized の捕捉 ---
    onResponseError({ response, options }) {
      if (response && response.status === 401) {
        const url = response.url;

        // 認証フロー内のリクエストはログアウトをスキップ
        const isAuthRelated =
          url.includes("/login") ||
          url.includes("/register") ||
          url.includes("/sanctum/csrf-cookie");
        const isSkipAutoLogoutFlag =
          options.context?.skipAutoLogout ||
          options.headers?.["X-Skip-Auto-Logout"] === "true";

        if (isAuthRelated || isSkipAutoLogoutFlag) {
          console.warn(
            `⚠️ [GLOBAL 401 INTERCEPTOR] 401エラーを捕捉したが、自動ログアウトをスキップ (Auth Related or Skip Flag): ${url}`
          );
          return;
        }

        console.error(
          `🚨 [GLOBAL 401 INTERCEPTOR] 401エラーを捕捉: ${url}。強制ログアウト処理を実行します。`
        );

        // useAuth() を呼び出し、clearToken 関数を取得して実行
        const { clearToken } = useAuth();
        clearToken();

        // ログインページへリダイレクト
        if (process.client) {
          return navigateTo("/login", { replace: true });
        }
      }
    },
  });

  // カスタム $fetch インスタンスを Nuxt App のヘルパーとして提供
  nuxtApp.provide("api", customFetch);
});
