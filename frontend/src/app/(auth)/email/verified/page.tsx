"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

export default function EmailVerifiedPage() {
  const router = useRouter();

  useEffect(() => {
    const timer = setTimeout(() => {
      router.replace("/login");
    }, 2000); // ← 2秒（好みで 1000〜3000）

    return () => clearTimeout(timer);
  }, [router]);

  return (
    <div className="flex min-h-screen items-center justify-center">
      <div className="text-center space-y-4">
        <h1 className="text-xl font-semibold">メール認証が完了しました</h1>
        <p className="text-gray-600">ログイン画面へ移動します…</p>
      </div>
    </div>
  );
}
