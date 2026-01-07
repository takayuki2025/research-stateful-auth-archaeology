// frontend/resources/js/app.jsx
import "../css/app.css"; // CSSもここでインポート
import React from "react";
import { createRoot } from "react-dom/client";

function App() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center text-center">
      <h1 className="text-5xl font-bold text-green-400 mb-4">
        🎉 Vite + React + Laravel
      </h1>
      <p className="text-xl text-gray-300">
        フロントエンド（Viteポート5173）からの表示に成功しました！
      </p>
      <div className="mt-8 p-4 border border-gray-600 rounded bg-gray-800">
        <p>
          このコンポーネントは <code>frontend/resources/js/app.jsx</code>{" "}
          にあります。
        </p>
      </div>
    </div>
  );
}

const container = document.getElementById("app");
if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
