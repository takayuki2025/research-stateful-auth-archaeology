import { useAuthStore } from "#imports";

/**
 * 認証済みユーザーのみアクセスを許可するミドルウェア。
 * 認証されていない場合、ログインページへリダイレクトします。
 */
export default defineNuxtRouteMiddleware((to, from) => {
  // Piniaストアから認証状態を取得
  const authStore = useAuthStore();

  // 認証トークンがない、またはユーザー情報がない場合
  if (!authStore.isAuthenticated) {
    // ★ ログを出力して何が起こっているかを追跡
    console.warn(
      `[Auth Middleware] Access denied for route: ${to.fullPath}. Redirecting to /login.`
    );

    // ログインページへリダイレクト
    // UX改善: ログインが成功した後に、ユーザーがアクセスしようとした元のページ(to.fullPath)に戻れるように、
    // 'redirect'クエリパラメータを付与します。
    return navigateTo({
      path: "/login",
      query: { redirect: to.fullPath },
    });
  }

  // 認証済みであればそのまま続行
});
