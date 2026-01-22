"use client";

// 互換用 shim
// 既存ページが "@/ui/auth/useAuth" を import してもビルドが落ちないようにする。
// 実体は AuthProvider 側の useAuth を正として再エクスポートする。
export { useAuth } from "@/ui/auth/AuthProvider";
