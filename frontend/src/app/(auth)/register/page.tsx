"use client";

import React, { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/useAuth";

export default function RegisterPage() {
  const router = useRouter();
  const { register, isLoading } = useAuth();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [apiError, setApiError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setApiError("");
    setIsSubmitting(true);

    if (!email || !password || !name) {
      setApiError("すべての必須項目を入力してください。");
      setIsSubmitting(false);
      return;
    }
    if (password !== passwordConfirmation) {
      setApiError("パスワードが一致しません。");
      setIsSubmitting(false);
      return;
    }

    try {
      const result = await register({ name, email, password });

      if (result.needsEmailVerification) {
        router.push("/email/verify?from=register");
      } else {
        router.replace("/");
      }

      console.log("[RegisterPage] REGISTER result:", result);

      // AuthService.register は { needsEmailVerification: true } を返す設計
      if (result?.needsEmailVerification) {
        router.push("/email/verify");
        return;
      }

      // 既にメール認証済みの場合など
      router.push("/mypage/profile");
    } catch (e: any) {
      console.error("[RegisterPage] registration failed:", e);
      setApiError(e?.message || "登録に失敗しました。もう一度お試しください。");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="w-full max-w-xl p-8 bg-white rounded-xl shadow-2xl mx-auto z-10 mt-10 mb-8">
      <h2 className="text-center text-3xl font-bold text-gray-800 mb-6 border-b pb-3">
        会員登録
      </h2>

      {apiError && (
        <div className="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm font-medium">
          {apiError}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-4">
        {/* ユーザー名 */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            ユーザー名
          </label>
          <input
            type="text"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        {/* メール */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            メールアドレス
          </label>
          <input
            type="email"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        {/* パスワード */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            パスワード
          </label>
          <input
            type="password"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        {/* 確認用パスワード */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            確認用パスワード
          </label>
          <input
            type="password"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            required
            disabled={isSubmitting || isLoading}
          />
        </div>

        {/* 登録ボタン */}
        <div className="pt-2">
          <button
            type="submit"
            disabled={isSubmitting || isLoading}
            className="w-full bg-red-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-red-700 transition shadow-lg disabled:bg-gray-400"
          >
            {isSubmitting ? "登録中..." : "登録する"}
          </button>
        </div>
      </form>

      <div className="mt-6 text-center">
        <Link
          href="/login"
          className="text-sm text-blue-500 hover:text-blue-700"
        >
          ログインはこちら
        </Link>
      </div>
    </div>
  );
}
