"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";
import { useState, type FormEvent } from "react";
import HeaderLeft from "./HeaderLeft";

export default function HeaderMain() {
  const router = useRouter();
  const { isAuthenticated, isLoading, logout } = useAuth();
  const [searchTerm, setSearchTerm] = useState("");

  const handleSearch = (e: FormEvent) => {
    e.preventDefault();
    if (!searchTerm.trim()) return;
    router.push(`/?all_item_search=${encodeURIComponent(searchTerm)}`);
  };

  return (
    <header className="bg-gradient-to-b from-[#0f172a] to-[#020617] text-white py-5 relative border-b border-white/5 shadow-xl">
      <div className="flex items-center px-16 h-full mx-auto max-w-[1850px]">
        <div className="flex-shrink-0 pt-0 opacity-90 hover:opacity-100 transition-opacity">
          <HeaderLeft href="/" />
        </div>

        <div className="flex flex-col ml-16 w-full">
          <div className="w-full border-b border-white/10 pb-3 flex justify-between items-end">
            <div className="flex flex-col">
              {/* タイトルエリア */}
              <div className="flex items-center space-x-6">
                <h1
                  className="text-[26px] font-[100] tracking-[0.6em] uppercase leading-none"
                  style={{ fontFamily: "'Playfair Display', serif" }}
                >
                  <span className="text-blue-400 font-[300]">O</span>mni{" "}
                  <span className="text-blue-400 font-[300]">C</span>ommerce{" "}
                  <span className="text-blue-400 font-[300]">C</span>ore
                </h1>

                {/* Connect / Creative：サイズを上げ、視認性を強化 */}
                <div className="flex flex-col text-[10px] leading-[1.4] tracking-[0.4em] uppercase text-slate-400 font-[200] border-l-2 border-blue-500/30 pl-5">
                  <div className="flex items-center">
                    <span className="text-blue-300 font-[500] mr-[1px]">C</span>
                    onnect
                  </div>
                  <div className="flex items-center">
                    <span className="text-blue-300 font-[500] mr-[1px]">C</span>
                    reative
                  </div>
                </div>
              </div>

              <div className="flex items-center space-x-6 text-[8px] font-[600] tracking-[0.4em] uppercase text-slate-300 italic whitespace-nowrap mt-3">
                <span>Powered by 　Atlas Kernel System</span>
                <span className="text-slate-600 font-normal not-italic">/</span>
                <span>Trust Ledger System</span>
              </div>
            </div>

            <div className="flex items-center space-x-10 text-[11px] font-[300] tracking-[0.3em] uppercase whitespace-nowrap pb-1">
              {isLoading ? null : isAuthenticated ? (
                <>
                  <button
                    onClick={() => logout()}
                    className="text-slate-400 hover:text-white transition-colors"
                  >
                    ログアウト
                  </button>
                  <Link
                    href="/mypage"
                    className="text-slate-400 hover:text-white transition-colors"
                  >
                    マイページ
                  </Link>
                  <Link
                    href="/sell"
                    className="px-4 py-1 border border-white/20 hover:border-white/50 hover:bg-white/5 transition-all"
                  >
                    出品
                  </Link>
                </>
              ) : (
                <Link
                  href="/login"
                  className="text-slate-400 hover:text-white transition-colors"
                >
                  ログイン
                </Link>
              )}
            </div>
          </div>

          <form onSubmit={handleSearch} className="mt-4 max-w-[700px]">
            <div className="relative group">
              <span className="absolute left-0 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                <svg
                  width="18"
                  height="18"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    strokeWidth="1.5"
                    strokeLinecap="round"
                  />
                </svg>
              </span>
              <input
                type="text"
                className="h-[38px] w-full pl-12 bg-transparent text-white border-b border-white/5 group-hover:border-white/20 focus:border-blue-500/50 focus:outline-none transition-all text-[13px] tracking-[0.1em] placeholder:text-slate-700 uppercase"
                placeholder="Search collection..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
              <div className="absolute left-0 bottom-[-1px] w-0 h-[1px] bg-gradient-to-r from-blue-500 to-transparent group-focus-within:w-full transition-all duration-1000"></div>
            </div>
          </form>
        </div>
      </div>
    </header>
  );
}
