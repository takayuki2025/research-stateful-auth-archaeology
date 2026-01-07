"use client";

import React, { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/useAuth";

export default function VerifyEmailPage() {
  const router = useRouter();
  const { user, isLoading } = useAuth();

  const [statusMessage, setStatusMessage] = useState<string | null>(null);
  const [isResending, setIsResending] = useState(false);

  /**
   * すでに認証済みなら追い出す
   */
  useEffect(() => {
    if (isLoading) return;
    if (!user) return;

    if (user.email_verified_at) {
      router.replace("/");
    }
  }, [isLoading, user, router]);

  const handleResend = async () => {
    setIsResending(true);
    setStatusMessage(null);

    try {
      const res = await fetch("/email/verification-notification", {
        method: "POST",
        credentials: "include",
        headers: { Accept: "application/json" },
      });

      if (!res.ok) {
        const data = await res.json();
        throw new Error(data?.message ?? "再送に失敗しました。");
      }

      setStatusMessage("認証メールを再送しました。メールをご確認ください。");
    } catch (e: any) {
      setStatusMessage(e.message || "再送に失敗しました。");
    } finally {
      setIsResending(false);
    }
  };

  if (isLoading) {
    return <div className="mt-20 text-center">読み込み中...</div>;
  }

  return (
    <div className="min-h-screen flex justify-center items-start pt-20 bg-gray-50">
      <div className="w-full max-w-xl p-8 bg-white rounded-lg shadow-xl">
        <h2 className="text-3xl font-bold text-indigo-600 text-center mb-6">
          メール認証のご案内
        </h2>

        <p className="text-center text-gray-700">
          <strong>{user?.email}</strong> 宛に認証メールを送信しました。
        </p>

        <p className="mt-3 text-center text-gray-600">
          メール内のリンクをクリックした後、
          <br />
          <strong>再度ログインしてください。</strong>
        </p>

        {statusMessage && (
          <div className="mt-6 p-3 bg-blue-50 text-blue-700 rounded text-center">
            {statusMessage}
          </div>
        )}

        <div className="mt-8 space-y-3">
          <button
            onClick={handleResend}
            disabled={isResending || !user}
            className="w-full bg-indigo-600 text-white py-3 rounded font-bold disabled:opacity-50"
          >
            認証メールを再送
          </button>

          <button
            onClick={() => router.push("/login")}
            className="w-full border py-3 rounded font-semibold"
          >
            ログインページへ
          </button>

          {!user && (
            <div className="mt-4 p-3 bg-yellow-50 text-yellow-700 text-sm rounded text-center">
              認証メールを再送するには、一度ログインしてください。
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
