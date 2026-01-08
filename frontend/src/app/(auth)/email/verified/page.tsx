"use client";

import { useEffect } from "react";

export default function EmailVerifiedPage() {
  useEffect(() => {
    // Cookie 状態に依存しない「確実な遷移」
    setTimeout(() => {
      window.location.replace("/login?verified=1");
    }, 300);
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center">
      <div>
        <h1>メール認証が完了しました</h1>
        <p>ログイン画面へ移動します…</p>
      </div>
    </div>
  );
}
