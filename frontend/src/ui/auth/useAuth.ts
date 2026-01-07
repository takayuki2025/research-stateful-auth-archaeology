"use client";

import { useContext } from "react";
import { AuthContext } from "./AuthProvider";
import type { AuthContextType } from "./AuthContext";

export function useAuth(): AuthContextType {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
