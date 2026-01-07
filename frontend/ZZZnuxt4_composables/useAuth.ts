import { ref, computed } from "vue";
import { useNuxtApp } from "#app";

// Firebase Auth インスタンスの型定義
type FirebaseAuthInstance = any;

// グローバルでシングルトンとして使用するためのカスタムComposable
export const useAuth = () => {
  // --- Sanctumトークン管理ロジック (変更なし) ---
  const initialToken =
    (typeof window !== "undefined"
      ? localStorage.getItem("sanctum_token")
      : null) || null;
  const token = ref<string | null>(initialToken);
  const isAuthenticated = computed(() => !!token.value);

  // --- Firebase Auth インスタンスの安全なゲッター (強化) ---

  /**
   * 注入された $firebaseAuth インスタンスを安全に取得します。
   * プラグインの実行タイミングに関わらず、アクセス時点でインスタンスが注入されているかをチェックします。
   * @returns FirebaseAuthInstance | null
   */
  const getFirebaseAuth = (): FirebaseAuthInstance | null => {
    // サーバーサイドでは絶対にAuthインスタンスにアクセスしない
    if (process.server) {
      return null;
    }

    const nuxtApp = useNuxtApp();

    // ★ 修正: 注入されたプロパティを安全にチェック
    const authInstance = (nuxtApp as any).$firebaseAuth as
      | FirebaseAuthInstance
      | undefined;

    // `$firebaseAuth` が undefined の場合は、まだ注入されていないか、
    // 非常に早い段階での呼び出しであることを示す
    if (!authInstance) {
      console.warn(
        "⚠️ [useAuth:getFirebaseAuth] $firebaseAuth が未解決です。呼び出し箇所を 'onMounted' または非同期処理内で実行してください。"
      );
      return null;
    }

    return authInstance;
  };

  // --- Sanctumトークン操作ロジック (変更なし) ---

  const setToken = (newToken: string) => {
    token.value = newToken;
    if (typeof window !== "undefined") {
      localStorage.setItem("sanctum_token", newToken);
    }
    console.log("[Auth] Sanctum Token set successfully.");
  };

  const clearToken = () => {
    token.value = null;
    if (typeof window !== "undefined") {
      localStorage.removeItem("sanctum_token");
    }
    console.log("[Auth] Sanctum Token cleared.");

    // Firebase Auth のログアウトを試みる
    const auth = getFirebaseAuth();
    if (auth && typeof auth.signOut === "function") {
      console.log("[Auth] Attempting Firebase sign-out.");
      // auth.signOut(); // 実際の処理は呼び出し側で実装
    }
  };

  return {
    token,
    isAuthenticated,
    setToken,
    clearToken,
    getFirebaseAuth,
  };
};
