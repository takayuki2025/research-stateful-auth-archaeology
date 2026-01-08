"use client";

import Link from "next/link";
import Image from "next/image";
import { useAuth } from "@/ui/auth/useAuth";
import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";

export default function HeaderMain() {
  const { isAuthenticated, isLoading, logout } = useAuth();
  const router = useRouter();
  const [searchTerm, setSearchTerm] = useState("");

  const handleLogout = async () => {
    await logout();
    router.replace("/login");
  };

  const handleSearch = (e: FormEvent) => {
    e.preventDefault();
    if (!searchTerm.trim()) return;
    router.push(`/?q=${encodeURIComponent(searchTerm)}`);
  };

  return (
    <header className="bg-black h-[70px] shadow-md">
      <div className="flex items-center h-full mx-auto max-w-[1300px] px-6">
        {/* ロゴ */}
        <div
          className="flex items-center w-[200px] cursor-pointer"
          onClick={() => router.push("/")}
        >
          <Image
            src="/image_icon/logo.svg"
            alt="ロゴ"
            width={200}
            height={40}
            priority
          />
        </div>

        {/* 🔍 検索（ログイン時のみ） */}
        {!isLoading && isAuthenticated && (
          <form onSubmit={handleSearch} className="flex items-center ml-8">
            <input
              className="h-[36px] w-[360px] px-4 rounded"
              placeholder="なにをお探しですか？"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </form>
        )}

        {/* 右側 */}
        <div className="flex items-center ml-auto space-x-6 pr-2">
          {isLoading ? null : isAuthenticated ? (
            <>
              <button onClick={handleLogout} className="text-white">
                ログアウト
              </button>
              <Link href="/mypage/profile" className="text-white">
                マイページ
              </Link>
            </>
          ) : (
            <Link href="/login" className="text-white">
              ログイン
            </Link>
          )}
        </div>
      </div>
    </header>
  );
}
