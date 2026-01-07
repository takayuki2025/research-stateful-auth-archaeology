"use client";

export async function logout(): Promise<void> {
  await fetch("/logout", {
    method: "POST",
    credentials: "include", // 🔥 Sanctum 必須
    headers: {
      Accept: "application/json",
    },
  });
}
