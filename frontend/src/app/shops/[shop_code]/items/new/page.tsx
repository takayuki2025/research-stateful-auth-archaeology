"use client";

import { useState } from "react";
import { useRouter, useParams } from "next/navigation";

export default function NewItemPage() {
  const router = useRouter();
  const params = useParams();
  const shopCode = params.shop_code as string;

  // 入力フォームの状態
  const [form, setForm] = useState({
    name: "",
    price: "",
    brand: "",
    explain: "",
    condition: "new",
    category: "",
    item_image: "",
    remain: "1",
  });

  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  function handleChange(e: React.ChangeEvent<HTMLInputElement>) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError("");
    setSuccess("");

    try {
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_BASE_URL}/api/shops/${shopCode}/items`,
        {
          method: "POST",
          credentials: "include", // Sanctumで必須
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            ...form,
            price: Number(form.price),
            remain: Number(form.remain),
            category: [form.category], // Laravel側が array を想定
          }),
        },
      );

      const data = await res.json();

      if (!res.ok) {
        setError(data.error ?? "登録失敗");
        return;
      }

      setSuccess("商品登録が完了しました！");

      // 商品一覧へ戻る
      router.push(`/shops/${shopCode}/`);
    } catch (err) {
      setError("通信エラーが発生しました");
    }
  }

  return (
    <div style={{ padding: 20 }}>
      <h1>{shopCode} に商品追加</h1>

      {error && <p style={{ color: "red" }}>{error}</p>}
      {success && <p style={{ color: "green" }}>{success}</p>}

      <form
        onSubmit={handleSubmit}
        style={{ display: "flex", flexDirection: "column", width: 300 }}
      >
        <input
          name="name"
          placeholder="商品名"
          value={form.name}
          onChange={handleChange}
          required
        />
        <input
          name="price"
          placeholder="価格"
          value={form.price}
          onChange={handleChange}
          required
        />
        <input
          name="brand"
          placeholder="ブランド"
          value={form.brand}
          onChange={handleChange}
        />
        <input
          name="explain"
          placeholder="説明"
          value={form.explain}
          onChange={handleChange}
          required
        />
        <input
          name="condition"
          placeholder="状態(new/good/used)"
          value={form.condition}
          onChange={handleChange}
        />
        <input
          name="category"
          placeholder="カテゴリ（例：家電）"
          value={form.category}
          onChange={handleChange}
        />
        <input
          name="item_image"
          placeholder="商品画像URL"
          value={form.item_image}
          onChange={handleChange}
        />
        <input
          name="remain"
          placeholder="在庫数"
          value={form.remain}
          onChange={handleChange}
        />

        <button type="submit" style={{ marginTop: 15 }}>
          登録する
        </button>
      </form>
    </div>
  );
}
