import { useNuxtApp, navigateTo, useRuntimeConfig } from "#app";
import { useAuthStore } from "@/stores/auth";
import { useAuth } from "~/composables/useAuth";

/**
 * カスタムAPIリクエストを行うためのコンポーザブル。
 * 認証済みエンドポイント向けに、認証エラー(401)のハンドリングとBearerトークンの付与を一元化します。
 */
export const useApi = () => {
  const { $api } = useNuxtApp();
  const authStore = useAuthStore();
  const { token: localToken } = useAuth(); // Bearerトークンを取得

  if (typeof $api !== "function") {
    console.error(
      "CRITICAL: $api instance is missing. Check plugins/api-interceptor.client.ts."
    );
    throw new Error("API instance not available.");
  }

  /**
   * 認証が必要なAPIエンドポイントにリクエストを送信します。
   * @param url APIエンドポイント（例: '/mypage/profile_update'）。baseURLは自動で付与されます。
   * @param options useFetch/Nuxt $apiのオプション
   * @returns APIレスポンスデータ
   */
  const authenticatedFetch = async (url: string, options: any = {}) => {
    // ★★★ 修正ポイント: apiBaseUrl を使った絶対URL生成を削除 ★★★
    // $api が baseURL を正しく持っているため、単に url を渡します
    const apiPath = url.startsWith("/") ? url : "/" + url;

    console.log(`[useApi] 相対リクエストパス: ${apiPath}`); // デバッグログ

    // 1. Bearerトークンをヘッダーに明示的に付与
    // NOTE: plugins/api-interceptor.client.ts で認証を処理することも可能ですが、
    // ここでBearerトークンを付与するロジックを尊重します
    if (localToken.value) {
      options.headers = {
        ...options.headers,
        Authorization: `Bearer ${localToken.value}`,
      };
      console.log(
        `[useApi] Authorization Bearer Token set. (Bearer Token Auth Mode)`
      );
    } else {
      console.warn(
        `[useApi] Bearer Token is missing for ${apiPath}. Proceeding without token.`
      );
    }

    try {
      // 2. 実際のAPIリクエスト実行
      // $apiがpluginsで設定されたbaseURL (4430/api) を自動で使います
      const response = await $api(apiPath, options);
      return response;
    } catch (error: any) {
      console.error(`[useApi] APIリクエスト失敗 (${apiPath}):`, error);

      if (error.response && error.response.status === 401) {
        // 401 Unauthorized エラーの場合、ログインページへリダイレクト
        console.log("[useApi] 401エラーを検知。ログインページへリダイレクト。");
        await authStore.logout();
        await navigateTo("/login");

        // Promise.rejectでチェーンを中断
        return Promise.reject({
          status: 401,
          message: "認証セッションが切れました。",
        });
      }

      throw error;
    }
  };

  return {
    authenticatedFetch,
  };
};
