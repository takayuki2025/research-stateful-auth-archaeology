"use client";

import { AuthProvider } from "@/ui/auth/AuthProvider";
import type { ReactNode } from "react";

export default function ClientWrapper({ children }: { children: ReactNode }) {
  return <AuthProvider>{children}</AuthProvider>;
}
