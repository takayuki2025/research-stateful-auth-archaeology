import { defineNuxtPlugin } from "#app";
import { useAuthStore } from "~/stores/auth";

const SHOULD_BLOCK_NUXT_START = true;

export default defineNuxtPlugin(async (nuxtApp) => {
  if (process.server) return;

  // 🚀 Firebaseが初期化されるのを待つ
  console.log("[Auth Plugin] Firebaseサービスの準備を確認中...");
  while (!nuxtApp.$firebaseAuth) {
    await new Promise((r) => setTimeout(r, 50));
  }
  console.log(
    "✅ [Auth Plugin] Firebaseサービス準備完了。initAuthを実行します。"
  );

  const authStore = useAuthStore();

  console.log("[Auth Plugin] Firebase認証リスナーを設定 (initAuth) します。");
  await authStore.initAuth();

  if (SHOULD_BLOCK_NUXT_START) {
    console.log("[Auth Plugin] 設定に基づき、認証状態の解決を待機します...");
    await authStore.waitForAuthResolution();
    console.log("✅ [Auth Plugin] 認証の初期化と解決が完了しました。");

    try {
      await authStore.getSanctumCsrfToken();
      console.log("✅ [Auth Plugin] Sanctum CSRF Tokenを取得しました。");
    } catch (e) {
      console.warn("[Auth Plugin] Sanctum CSRF Token取得に失敗:", e);
    }
  } else {
    console.log("✅ [Auth Plugin] 非ブロッキングモードで起動します。");
  }
});
