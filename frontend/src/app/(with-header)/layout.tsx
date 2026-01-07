"use client";

import HeaderMain from "@/components/layout/HeaderMain";

export default function WithHeaderLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <>
      <HeaderMain />
      <main className="pt-[70px]">{children}</main>
    </>
  );
}
