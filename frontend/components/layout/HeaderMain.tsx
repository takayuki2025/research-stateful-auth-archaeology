"use client";

import Link from "next/link";
import Image from "next/image";
import { useAuth } from "@/ui/auth/useAuth";
import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";

export default function HeaderMain() {
  const { isAuthenticated, logout, isLoading } = useAuth();
  const router = useRouter();
  const [searchTerm, setSearchTerm] = useState("");

  const handleLogout = async () => {
    await logout();
    router.push("/login");
  };

  const handleSearch = (e: FormEvent) => {
    e.preventDefault();

    if (searchTerm.trim().length === 0) {
      router.push("/");
      return;
    }

    router.push(`/?all_item_search=${encodeURIComponent(searchTerm.trim())}`);
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
            alt="会社名"
            width={200}
            height={40}
            className="object-contain"
            priority
          />
        </div>

        {/* 検索フォーム */}
        <form onSubmit={handleSearch} className="flex items-center ml-8">
          <input
            type="text"
            className="
              h-[36px]
              w-[360px]
              px-4
              rounded
              text-gray-900
              focus:outline-none
            "
            placeholder="なにをお探しですか？"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </form>

        {/* 右側メニュー */}
        <div className="flex items-center ml-auto space-x-6 pr-2">
          {isLoading ? null : isAuthenticated ? (
            <>
              <button onClick={handleLogout} className="text-white">
                ログアウト
              </button>
              <Link href="/mypage?page=sell" className="text-white">
                マイページ
              </Link>
              <Link
                href="/sell"
                className="bg-white text-black px-4 py-1 rounded font-semibold"
              >
                出品
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
