"use client";

import React, { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

export default function LoginPage() {
  const router = useRouter();
  const { login, isLoading } = useAuth();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [apiError, setApiError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setApiError("");
    setIsSubmitting(true);

    try {
      /**
       * 🔐 認証のみを行う
       * - 状態同期は AuthProvider
       * - 遷移判断は useAuthGuard
       */
      await login({ email, password });

      // ✅ ここでは必ずトップへ
      router.replace("/");
    } catch {
      setApiError("ログインに失敗しました");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <div className="w-full max-w-xl p-8 mx-auto mt-20 bg-white rounded-xl shadow-xl">
      <h2 className="mb-6 text-3xl font-bold text-center border-b pb-3">
        ログイン
      </h2>

      {apiError && (
        <div className="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded">
          {apiError}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block mb-1 text-sm">メールアドレス</label>
          <input
            type="email"
            className="w-full px-4 py-2 border rounded"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        <div>
          <label className="block mb-1 text-sm">パスワード</label>
          <input
            type="password"
            className="w-full px-4 py-2 border rounded"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        <button
          type="submit"
          disabled={isSubmitting || isLoading}
          className="w-full py-3 bg-red-600 text-white rounded"
        >
          {isSubmitting ? "ログイン中..." : "ログインする"}
        </button>
      </form>

      <div className="mt-6 text-center">
        <Link href="/register" className="text-blue-500 text-sm">
          会員登録はこちら
        </Link>
      </div>
    </div>
  );
}
