import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.tsx";
// このプロジェクトでは、Tailwind CSSのインポートは必要ありません。
// ViteのビルドプロセスとPostCSSが自動的に処理します。

// デモ用の基本的なAppコンポーネントです
function InitialApp() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 p-4">
      <div className="text-center p-8 bg-white shadow-xl rounded-xl">
        <h1 className="text-3xl font-bold text-indigo-600 mb-4">
          ✨ React + Vite + TypeScript 起動成功！
        </h1>
        <p className="text-lg text-gray-700">
          すべての依存関係が正しく設定されました。
        </p>
        <p className="mt-2 text-sm text-gray-500">
          次のステップで、この{" "}
          <code className="bg-gray-200 p-1 rounded">App.tsx</code>{" "}
          を書き換えます。
        </p>
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <InitialApp />
  </React.StrictMode>
);
