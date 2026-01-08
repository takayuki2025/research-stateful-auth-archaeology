"use client";

import { useState } from "react";
import { useAuth } from "@/ui/auth/useAuth";

export default function VerifyEmailPage() {
  const { user, apiClient, isLoading, isReady } = useAuth();
  const [statusMessage, setStatusMessage] = useState<string | null>(null);
  const [isResending, setIsResending] = useState(false);

  const handleResend = async () => {
    if (!apiClient) return;

    setIsResending(true);
    setStatusMessage(null);

    try {
      await apiClient.post("/email/verification-notification");
      setStatusMessage("認証メールを再送しました。");
    } catch {
      setStatusMessage("再送に失敗しました。");
    } finally {
      setIsResending(false);
    }
  };

  if (isLoading || !isReady) {
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
          メール内のリンクをクリックして認証を完了してください。
        </p>

        {statusMessage && (
          <div className="mt-6 p-3 bg-blue-50 text-blue-700 rounded text-center">
            {statusMessage}
          </div>
        )}

        <button
          onClick={handleResend}
          disabled={isResending || !user}
          className="mt-6 w-full bg-indigo-600 text-white py-3 rounded font-bold"
        >
          認証メールを再送
        </button>
      </div>
    </div>
  );
}
