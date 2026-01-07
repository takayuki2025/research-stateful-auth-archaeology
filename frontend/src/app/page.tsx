"use client";

import Link from "next/link";
import { useAuth } from "@/ui/auth/useAuth";

export default function Home() {
  const { user, isLoading, logout } = useAuth();

  if (isLoading) return <div>Loading...</div>;

  return (
    <div className="p-10 space-y-4">
      <h1 className="text-2xl font-bold">Top Page</h1>

      {user ? (
        <>
          <p>ログイン中: {user.email}</p>
          <button onClick={logout} className="text-red-600">
            ログアウト
          </button>
          <div>
            <Link href="/mypage">マイページ</Link>
          </div>
        </>
      ) : (
        <>
          <Link href="/login">ログイン</Link>
          <br />
          <Link href="/register">新規登録</Link>
        </>
      )}
    </div>
  );
}