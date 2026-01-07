"use client";

import Link from "next/link";
import Image from "next/image";

export default function HeaderAuth() {
  return (
    <header className="bg-black h-[70px] shadow-md">
      <div className="flex items-center px-4 h-full mx-auto max-w-[1300px]">
        <Link href="/" className="relative h-[50px] w-[250px] flex-shrink-0">
          <Image
            className="object-contain"
            src="/image_icon/logo.svg"
            alt="会社名"
            fill
            loading="eager"
          />
        </Link>

        <div className="flex ml-auto items-center space-x-2">
          <Link
            href="/register"
            className="text-white text-base py-1 px-3 border border-black hover:bg-white hover:text-black"
          >
            会員登録
          </Link>

          <Link
            href="/login"
            className="text-white text-base py-1 px-3 border border-black hover:bg-white hover:text-black"
          >
            ログイン
          </Link>
        </div>
      </div>
    </header>
  );
}
