"use client";

import HeaderMain from "./HeaderMain";

export function HeaderMainFrame({ children }: { children: React.ReactNode }) {
  return (
    <>
      <HeaderMain />
      <main className="pt-[70px]">{children}</main>
    </>
  );
}
