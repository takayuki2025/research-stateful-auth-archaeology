"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { apiClient } from "@/lib/apiClient";

export default function RegisterPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);

    try {
      await apiClient.get("/sanctum/csrf-cookie");
      await apiClient.post("/register", { email, password });
      router.replace("/");
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="max-w-md mx-auto mt-20 space-y-4">
      <h1 className="text-xl font-bold">新規登録</h1>

      <input
        className="border w-full px-3 py-2"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="email"
        required
      />

      <input
        className="border w-full px-3 py-2"
        type="password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        placeholder="password"
        required
      />

      <button className="w-full bg-green-600 text-white py-2">登録</button>
    </form>
  );
}
