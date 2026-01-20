"use client";

import React, { useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

export default function LoginPage() {
  const router = useRouter();
  const { login, isLoading, apiClient, refresh } = useAuth() as any;

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [apiError, setApiError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setApiError("");
    setIsSubmitting(true);

    try {
      await login({ email, password });

      // ✅ ログイン時刻（UI表示用）
      try {
        localStorage.setItem("last_login_at", new Date().toISOString());
      } catch {
        // ignore
      }

      // ✅ 認証状態の同期完了を待つ（Sanctum等の直後競合を避ける）
      if (typeof refresh === "function") {
        await refresh();
      }

      // ✅ apiClient は内部で /api prefix を付けている可能性があるため /me を使う
      const me = await apiClient.get("/me");

      // 1) プロフィール未完了ならオンボーディングへ
      if (me?.profile_completed === false) {
        router.replace("/mypage/profile");
        return;
      }

      // 2) shop_roles があればショップ導線へ
      const shopRoles = Array.isArray(me?.shop_roles) ? me.shop_roles : [];
      if (shopRoles.length > 0) {
        const primary = shopRoles[0];
        if (primary?.shop_code) {
          router.replace(`/shops/${primary.shop_code}/dashboard`);
          return;
        }
      }

      // 3) それ以外は一般トップへ
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

      <div className="mt-6 text-center space-y-2">
        <Link href="/register" className="text-blue-500 text-sm block">
          会員登録はこちら
        </Link>

        {/* 管理者コンソール（別アプリ）へのリンク（開発用） */}
        <a
          href="http://localhost:3001/admin/trustledger/kpis/global"
          className="text-gray-600 text-xs underline block"
        >
          管理者/開発者コンソールへ（TrustLedger）（開発用）
        </a>
      </div>
    </div>
  );
}
