import { defineNuxtPlugin } from "#app";
import { useAuthStore } from "~/stores/auth";
import { FetchError } from "ofetch"; // ofetchからFetchErrorをインポート

/**
 * 401 Unauthorized エラーをグローバルに捕捉し、Auth Storeを介して強制ログアウトを実行するプラグイン。
 *
 * このプラグインは、fetchUser()だけでなく、アプリケーション内のあらゆる認証済みAPIコールが
 * 401を返した場合に、確実にセッションをクリアし、無限ループを防ぐために使用されます。
 */
export default defineNuxtPlugin((nuxtApp) => {
  if (process.server) {
    return;
  }

  const authStore = useAuthStore();
  // Nuxt 3でルーターを取得する方法
  const router = nuxtApp.$router;

  // $fetchのオプションを拡張し、グローバルなエラーハンドラーを登録
  nuxtApp.hook("app:created", () => {
    // $fetcherのインスタンスにエラーインターセプターを設定
    const originalFetch = globalThis.$fetch;

    // グローバルな $fetch をラップ
    globalThis.$fetch = async (request, options = {}) => {
      try {
        // 元の $fetch を呼び出し
        const response = await originalFetch(request, options);
        return response;
      } catch (error) {
        // FetchError（ofetchによってラップされたエラー）の場合のみ処理
        if (error instanceof FetchError) {
          const status = error.response?.status;

          // ★ 追加: リクエストURLを取得し、/logout APIからの401かどうかをチェック
          const requestUrl = error.request.toString();
          const isLogoutRequest =
            requestUrl.endsWith("/logout") || requestUrl.endsWith("/logout/");

          // 401 Unauthorized をチェック
          if (status === 401) {
            // ★ ログアウトリクエストからの401は無視し、無限ループを防ぐ
            if (isLogoutRequest) {
              console.warn(
                `⚠️ [GLOBAL 401 INTERCEPTOR] /logout APIが401を返しました。無限ループを防ぐため、強制ログアウト処理をスキップします。`
              );
              // エラーを再スローし、logout()内のcatchで処理させる
              throw error;
            }

            console.error(
              `🚨 [GLOBAL 401 INTERCEPTOR] 401エラーを捕捉: ${error.request}。強制ログアウトを実行します。`
            );

            // 1. 認証状態をクリア
            // logout()はFirebase/Laravelの両方でセッション破棄を試み、ローカル状態をクリアする
            await authStore.logout();

            // 2. ログインページへリダイレクト
            // 現在のルートがすでに /login でない場合のみリダイレクト
            if (router && router.currentRoute.value.path !== "/login") {
              router.push("/login");
            }
          }
        }
        // 401以外のエラー、または/logoutからの401はそのまま再スローして、呼び出し元のコンポーネントで処理させる
        throw error;
      }
    };
  });
});
