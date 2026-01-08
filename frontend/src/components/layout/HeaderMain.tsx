"use client";

import Link from "next/link";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/useAuth";
import { useState, type FormEvent } from "react";

export default function HeaderMain() {
  const router = useRouter();
  const { isAuthenticated, isLoading, logout } = useAuth();
  const [searchTerm, setSearchTerm] = useState("");

  const handleLogout = async () => {
    await logout();
  };

  const handleSearch = (e: FormEvent) => {
    e.preventDefault();
    if (!searchTerm.trim()) return;

    router.push(`/?all_item_search=${encodeURIComponent(searchTerm)}`);
  };

  return (
    <header className="bg-black h-[70px] shadow-md">
      <div className="flex items-center p-[20px_15px] h-full mx-auto max-w-[1300px]">
        {/* ロゴ */}
        <div
          className="relative w-[250px] h-[50px] flex-shrink-0 cursor-pointer"
          onClick={() => router.push("/")}
        >
          <Image
            src="/image_icon/logo.svg"
            alt="会社名"
            fill
            className="object-contain"
            priority
          />
        </div>

        {/* 🔍 検索フォーム（未認証でも常に表示） */}
        <form onSubmit={handleSearch} className="ml-[50px] flex items-center">
          <input
            type="text"
            className="h-[30px] w-[300px] px-3 py-1 text-gray-800 rounded"
            placeholder="なにをお探しですか？"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </form>

        {/* 右側メニュー */}
        <div className="flex items-center ml-auto space-x-2">
          {isLoading ? null : isAuthenticated ? (
            <>
              <button onClick={handleLogout} className="text-white">
                ログアウト
              </button>
              <Link href="/mypage?page=sell" className="text-white">
                マイページ
              </Link>
              <Link href="/sell" className="text-white">
                出品
              </Link>
            </>
          ) : (
            <Link href="/login" className="text-white">
              ログインへ
            </Link>
          )}
        </div>
      </div>
    </header>
  );
}
