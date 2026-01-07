"use client";

// import HeaderAuth from "@/components/layout/HeaderAuth";
import type { ReactNode } from "react";

export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <>
      {/* <HeaderAuth /> */}
      <main className="mx-auto max-w-[1300px] min-h-screen">{children}</main>
    </>
  );
}
