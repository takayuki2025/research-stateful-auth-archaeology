"use client";

import React, { useState, useEffect, useCallback } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";

import { useAuth } from "@/ui/auth/useAuth";
import { getImageUrl, IMAGE_TYPE, onImageError } from "@/utils/utils";
import styles from "./W-Mypage.module.css";

/**
 * /storage/ が二重になるのを防ぐための正規化関数
 */
const normalizeImagePath = (path?: string | null) => {
  if (!path) return null;
  return path.replace(/^\/?storage\//, "");
};

export default function Mypage() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const {
    isAuthenticated,
    isLoading: isAuthLoading,
    apiClient,
    logout,
  } = useAuth();

  const [user, setUser] = useState<any>(null);
  const [items, setItems] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  const page = searchParams.get("page") === "buy" ? "buy" : "sell";

  // -----------------------------
  // プロフィール取得
  // -----------------------------
  const fetchProfile = useCallback(async () => {
    if (!apiClient) return;
    try {
      const res = await apiClient.get("/mypage/profile");
      setUser(res.data.user ?? res.data);
    } catch (e: any) {
      if (e.response?.status === 401) {
        await logout();
        router.replace("/login");
      }
    }
  }, [apiClient, logout, router]);

  // -----------------------------
  // 出品 / 購入商品取得
  // -----------------------------
  const fetchItems = useCallback(async () => {
    if (!apiClient) return;
    setIsLoading(true);

    try {
      const endpoint = page === "sell" ? "/mypage/sell" : "/mypage/bought";
      const res = await apiClient.get(endpoint);
      setItems(res.data.items ?? []);
    } finally {
      setIsLoading(false);
    }
  }, [apiClient, page]);

  // -----------------------------
  // 初期ロード
  // -----------------------------
  useEffect(() => {
    if (isAuthLoading) return;

    if (!isAuthenticated) {
      router.replace("/login");
      return;
    }

    fetchProfile();
  }, [isAuthLoading, isAuthenticated, fetchProfile, router]);

  useEffect(() => {
    if (user) fetchItems();
  }, [user, fetchItems]);

  if (isAuthLoading || isLoading) {
    return <div className="text-center p-10">読み込み中...</div>;
  }

  if (!user) return null;

  return (
    <div className={styles.profile_page}>
      <div className={styles.profile_header}>
        <div className={styles.profile_header_1}>
          <img
            src={getImageUrl(normalizeImagePath(user.user_image))}
            onError={(e) => onImageError(e, user.name)}
          />

          <h2 className={`text-2xl font-bold ${styles.user_name_large_shift}`}>
            {user.name}
          </h2>

          <button
            onClick={() => router.push("/mypage/profile")}
            className="ml-auto px-4 py-2 border border-red-500 text-red-500 rounded"
          >
            プロフィールを編集
          </button>
        </div>

        <div className={styles.profile_header_2}>
          <Link
            href="/mypage?page=sell"
            className={
              page === "sell" ? styles.active_tab : styles.inactive_tab
            }
          >
            出品した商品
          </Link>

          <Link
            href="/mypage?page=buy"
            className={page === "buy" ? styles.active_tab : styles.inactive_tab}
          >
            購入した商品
          </Link>
        </div>
      </div>

      <div className={styles.items_select}>
        {items.length === 0 ? (
          <p className="text-center text-gray-500">
            {page === "sell"
              ? "出品した商品はありません"
              : "購入した商品はありません"}
          </p>
        ) : (
          items.map((item) => (
            <Link
              key={item.id}
              href={`/item/${item.id}`}
              className={styles.items_select_all}
            >
              <img
                src={getImageUrl(normalizeImagePath(item.item_image))}
                onError={(e) => onImageError(e, item.name)}
              />
              <div>{item.name}</div>
            </Link>
          ))
        )}
      </div>
    </div>
  );
}
