import { initializeApp, FirebaseApp } from "firebase/app";
import { getAuth, Auth } from "firebase/auth";
import { defineNuxtPlugin, useRuntimeConfig } from "#app";
import { useAuthStore } from "~/stores/auth";

// 既存の型定義を拡張し、NuxtAppのカスタムプロパティを追加
declare module "#app" {
  interface NuxtApp {
    $firebaseApp?: FirebaseApp;
    $firebaseAuth?: Auth;
  }
}

// このファイルは末尾の `.client` により、クライアントサイドでのみ実行されます。
export default defineNuxtPlugin(async (nuxtApp) => {
  console.log("🔥 [PLUG-IN STATUS] 00.firebase-service.client.ts が実行開始。");
  if (process.server) {
    return;
  }

  const TIMEOUT_MS = 5000; // 5秒のタイムアウトを設定 (ポーリングの最大待機時間)

  try {
    const config = useRuntimeConfig();

    const firebaseConfig = {
      apiKey: config.public.firebaseApiKey,
      authDomain: config.public.firebaseAuthDomain,
      projectId: config.public.firebaseProjectId,
      storageBucket: config.public.firebaseStorageBucket,
      messagingSenderId: config.public.firebaseMessagingSenderId,
      appId: config.public.firebaseAppId,
    };

    // 1. APIキーの存在チェック (必須)
    if (!firebaseConfig.apiKey || firebaseConfig.apiKey.length < 10) {
      console.error(
        "CRITICAL: Firebase API Keyが未設定または無効です。初期化をスキップします。"
      );
      nuxtApp.provide("firebaseAuth", null);
      return;
    }

    // 2. Firebase AppとAuthインスタンスの初期化と注入
    let app: FirebaseApp;
    let auth: Auth;
    if (!nuxtApp.$firebaseApp) {
      app = initializeApp(firebaseConfig);
      auth = getAuth(app);
      nuxtApp.provide("firebaseApp", app);
      nuxtApp.provide("firebaseAuth", auth);
    } else {
      app = nuxtApp.$firebaseApp;
      auth = nuxtApp.$firebaseAuth as Auth;
    }

    // 3. Piniaストアの取得
    const authStore = useAuthStore();

    // 4. ストアの初期化アクションを実行し、isAuthReadyフラグをポーリングで待機するロジック

    const authInitCheck = new Promise<void>(async (resolve) => {
      console.log(
        "⏳ [AuthCheck] Auth Storeの initAuth を実行し、isAuthReady の解決を待機中..."
      );

      // authStore.initAuth() を呼び出すことで、内部で onAuthStateChanged リスナーが設定され、
      // 状態の変更を待ち始める
      authStore.initAuth();

      // Simple Polling Logic (最大5秒間)
      const maxAttempts = 100; // 100回 x 50ms = 5000ms
      for (let i = 0; i < maxAttempts; i++) {
        // isAuthReady は Pinia Store の computed/ref なので .value が必要です
        if (authStore.isAuthReady) {
          console.log("✅ [AuthCheck] Auth Storeが初期化完了を報告しました。");
          resolve();
          return;
        }
        // 50ms 待機
        await new Promise((r) => setTimeout(r, 50));
      }

      // タイムアウトが発生した場合の処理
      console.warn(
        `⚠️ [AuthCheck Timeout] ${TIMEOUT_MS}ms 経過。認証状態が解決しないため、処理を続行します。`
      );

      // 🚨 CRITICAL FIX: タイムアウト時にストアの状態を強制的に 'Ready' に設定する
      if (!authStore.isAuthReady) {
        console.warn(
          "🚨 [CRITICAL FIX] 認証ストアの状態を強制的に 'Ready' に設定し、フリーズを解除します。"
        );
        authStore.isAuthReady = true;
      }

      resolve();
    });

    await authInitCheck;

    console.log(
      "✅ [PLUG-IN STATUS] 認証の初期解決 (またはタイムアウトによる強制続行) が完了しました。Nuxt Appの起動を継続します。"
    );
  } catch (error: any) {
    // 6. 初期化中のエラーをキャッチ
    console.error(
      `❌ CRITICAL: Firebase Initialization Failed: ${error.message}`,
      error
    );
    // エラーが発生した場合も強制的にReadyにして、ローディング画面から脱出
    const authStore = useAuthStore();
    authStore.isAuthReady = true;
  }
});
