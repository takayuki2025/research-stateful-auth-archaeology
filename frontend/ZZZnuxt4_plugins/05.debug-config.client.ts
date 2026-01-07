// このプラグインはクライアント側でのみ実行されます。
// Nuxtが正しくruntimeConfigを読み込んでいるかを確認するために使用します。
import { defineNuxtPlugin, useRuntimeConfig } from "#app";

export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig();

  // 開発環境でのみログを出力
  if (process.env.NODE_ENV === "development") {
    console.groupCollapsed("🌟 Nuxt Runtime Config (Debug)");
    console.log(
      "API Base URL (NUXT_PUBLIC_API_BASE_URL):",
      config.public.apiBaseUrl
    );
    console.log(
      "Asset Base URL (NUXT_PUBLIC_ASSET_BASE_URL):",
      config.public.assetBaseUrl
    );
    console.groupEnd();

    // 4431が含まれている場合はコンソールに大きな警告を表示
    if (config.public.apiBaseUrl && config.public.apiBaseUrl.includes("4431")) {
      console.error(
        '🚨 致命的な警告: API Base URLにまだ "4431" が含まれています。環境変数を再確認してください。'
      );
    }
  }
});
