"use client";

import { useRequireAuth } from "@/ui/auth/useRequireAuth";

export default function AuthRequiredLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  useRequireAuth();
  return <>{children}</>;
}
