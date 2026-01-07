import React from "react";
import Link from "next/link"; // NuxtLinkの代わりにNext.jsのLinkコンポーネントを使用

// =======================================================
// 1. スタイル定義 (CSS in JS / Tailwind CSS)
// =======================================================

/**
 * カスタムアニメーション用のCSS定義
 * Next.jsでは、グローバルCSSまたは styled-jsx (非推奨) / Tailwindのユーティリティで対応
 * 今回はグローバルCSSファイルに 'pulse-slow' を定義するか、
 * 以下のようにシンプルなユーティリティで対応することを推奨しますが、
 * ここではNuxtのCSSをTSXファイル内で再現します。
 */

// Tailwindで可能な限り再現し、足りないカスタムスタイルを定義
const customStyles = `
/* アニメーションの定義 */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

/* アイコンにアニメーションを適用 */
.animate-pulse-slow {
    animation: pulse-slow 3s infinite ease-in-out;
}
.shadow-3xl {
    box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.3);
}
`;

// =======================================================
// 2. コンポーネント定義
// =======================================================

export default function ThanksSellPage() {
  return (
    // Main container, centered on the screen
    <div className="flex items-center justify-center min-h-screen bg-gray-50">
      {/* Card container with shadow and rounded corners */}
      <div className="container mx-auto p-8 bg-white rounded-xl shadow-2xl max-w-lg text-center transform transition duration-500 hover:scale-[1.01] hover:shadow-3xl">
        {/* Confirmation Icon Section */}
        <div className="text-purple-600 mb-6">
          {/* SVG Checkmark Icon - Tailwindのanimate-pulseは高速なので、カスタムクラスを適用 */}
          <svg
            className="h-24 w-24 mx-auto animate-pulse-slow" // カスタムクラスを適用
            fill="currentColor"
            stroke="none"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            {/* SVG Path (チェックマーク付き円) */}
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        {/* Title and Message */}
        <h1 className="text-4xl font-extrabold mb-4 text-gray-900">
          ご出品ありがとうございます
        </h1>
        <p className="text-lg text-gray-600 mb-10">
          お客様の出品手続きは正常に完了しました。
          <span className="sm:hidden">
            <br />
          </span>
          商品がすぐに公開されるよう処理中です。
        </p>

        {/* Action Button (Next.js Link for routing) */}
        <Link
          href="/" // トップページへのルーティング
          className="inline-block bg-blue-600 text-white font-bold py-3 px-10 rounded-full shadow-lg hover:bg-blue-700 transition-colors duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50"
        >
          トップページに戻る
        </Link>
      </div>

      {/* カスタムCSSの適用 (styled-jsx または グローバルCSSを推奨) */}
      <style
        dangerouslySetInnerHTML={{
          __html: customStyles,
        }}
      />
    </div>
  );
}
