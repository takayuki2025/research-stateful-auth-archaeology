"use client";

import React, { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/useAuth";

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
      const result = await login({ email, password });

      if (!result.user.email_verified) {
        router.replace("/email/verify");
        return;
      }

      router.replace("/");
    } catch {
      setApiError("ログインに失敗しました");
    } finally {
      setIsSubmitting(false);
    }
  }

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-900 text-white">
        認証状態を確認中...
      </div>
    );
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
          />
        </div>

        <button
          type="submit"
          disabled={isSubmitting}
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
