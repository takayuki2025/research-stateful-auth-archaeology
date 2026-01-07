"use client";

import { AuthProvider } from "@/ui/auth/AuthProvider";
import HeaderMain from "@/components/layout/HeaderMain";

export default function ClientLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <AuthProvider>
      <HeaderMain />
      {children}
    </AuthProvider>
  );
}
