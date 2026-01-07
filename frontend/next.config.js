/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,

  // PPR / RSC キャッシュ
  cacheComponents: false,

  env: {
    NEXT_PUBLIC_FIREBASE_API_KEY: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
    NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN:
      process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
    NEXT_PUBLIC_FIREBASE_PROJECT_ID:
      process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
    NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET:
      process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
    NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID:
      process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
    NEXT_PUBLIC_FIREBASE_APP_ID: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
  },

  // Origin 統一型では、画像の取得先として backend を許可
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "localhost",
        port: "9000",
        pathname: "/storage/**",
      },
    ],
  },

  // 🔥🔥 これが Origin 統一の最重要ポイント（API プロキシ）
  async rewrites() {
    return [
      {
        source: "/api/:path*",
        destination: "https://localhost:9000/api/:path*",
      },
      {
        source: "/sanctum/:path*",
        destination: "https://localhost:9000/sanctum/:path*",
      },
    ];
  },

  // 🔥 mkcert を使用している場合に必須
  // Next.js dev server 自身も HTTPS として動けるようにする
  // serverRuntimeConfig: {},
  // experimental: {
  //   serverActions: {
  //     allowedOrigins: ["localhost:3000"],
  //   },
  // },
};

export default nextConfig;
