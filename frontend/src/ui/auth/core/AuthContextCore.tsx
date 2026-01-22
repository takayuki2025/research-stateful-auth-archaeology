"use client";

import { createContext, useContext } from "react";
import type { AuthContext } from "@/ui/auth/contracts";

export const AuthCtx = createContext<AuthContext | null>(null);

export function useAuth(): AuthContext {
  const ctx = useContext(AuthCtx);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
