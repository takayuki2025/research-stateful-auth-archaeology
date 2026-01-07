// import { defineNuxtConfig } from 'nuxt/config' が前提
export default defineNuxtConfig({
  // Nuxt 3/4では SSG のために ssr: true のみを保持
  ssr: true,

  // 【★修正箇所】target: "static" は削除する (Nuxt 3/4では非推奨/無効)
  // target: "static",

  // Nuxt 4 の設定を維持
  compatibilityDate: "2025-07-15",
  devtools: { enabled: true },

  // 静的ホスティングの基本設定
  routeRules: {
    "/**": { static: true }, // すべてのルートを静的にプリレンダリング
  },

  // nitro セクションは特に設定がなければ不要
  // nitro: {},
});
