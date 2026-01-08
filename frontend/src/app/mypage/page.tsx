"use client";

import React, { useState, useEffect, useCallback, useMemo } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import type { AxiosResponse } from "axios";

import { useAuth } from "@/ui/auth/useAuth";
import { getImageUrl, IMAGE_TYPE, onImageError } from "@/utils/utils";
import styles from "./W-Mypage.module.css";

/**
 * sell: 出品商品一覧
 * buy : 購入商品一覧
 */
type PageMode = "sell" | "buy";

type MypageItem = {
  row_id: string;
  item_id: number;
  name: string;
  item_image: string | null;
  order_id?: number | null;
  price?: number | null;
};

type ProfileUser = {
  id: number;
  display_name: string | null;
  user_image: string | null;
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

  const [user, setUser] = useState<ProfileUser | null>(null);
  const [items, setItems] = useState<MypageItem[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [profileLoaded, setProfileLoaded] = useState(false);

  // =============================
  // page 判定
  // =============================
  const page: PageMode = useMemo(() => {
    return searchParams.get("page") === "buy" ? "buy" : "sell";
  }, [searchParams]);

  // =============================
  // プロフィール取得（Gate しない）
  // =============================
  const fetchProfile = useCallback(async () => {
    if (!apiClient) return;

    try {
      const res: AxiosResponse<any> = await apiClient.get("/mypage/profile");
      const data = res.data?.user ?? null;
      setUser(data);
    } catch (e: any) {
      if (e.response?.status === 401) {
        await logout();
        router.replace("/login");
      }
    } finally {
      setProfileLoaded(true);
    }
  }, [apiClient, logout, router]);

  // =============================
  // 出品 / 購入商品取得
  // =============================
  const fetchItems = useCallback(async () => {
    if (!apiClient) return;

    setIsLoading(true);
    try {
      const endpoint = page === "sell" ? "/mypage/sell" : "/mypage/bought";
      const res: AxiosResponse<any> = await apiClient.get(endpoint);
      setItems((res.data?.items ?? []) as MypageItem[]);
    } finally {
      setIsLoading(false);
    }
  }, [apiClient, page]);

  // =============================
  // 初期ロード
  // =============================
  useEffect(() => {
    if (isAuthLoading) return;

    if (!isAuthenticated) {
      router.replace("/login");
      return;
    }

    fetchProfile();
  }, [isAuthLoading, isAuthenticated, fetchProfile, router]);

  useEffect(() => {
    if (profileLoaded) {
      fetchItems();
    }
  }, [profileLoaded, fetchItems]);

  // =============================
  // Loading
  // =============================
  if (isAuthLoading || !profileLoaded || isLoading) {
    return <div className="text-center p-10">読み込み中...</div>;
  }

  // ★ ここが超重要：null でも描画する
  const safeUser: ProfileUser = user ?? {
    id: 0,
    display_name: "",
    user_image: null,
  };

  // =============================
  // Render
  // =============================
  return (
    <div className={styles.profile_page}>
      <div className={styles.profile_header}>
        <div className={styles.profile_header_1}>
          <img
            src={getImageUrl(safeUser.user_image, IMAGE_TYPE.USER)}
            onError={onImageError}
            className={styles.user_image_css}
            alt="ユーザー画像"
          />

          <h2 className={`text-2xl font-bold ${styles.user_name_large_shift}`}>
            {safeUser.display_name ?? ""}
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
            <div key={item.row_id} className={styles.items_select_all}>
              <Link
                href={
                  page === "buy" && item.order_id
                    ? `/mypage/orders/${item.order_id}`
                    : `/item/${item.item_id}`
                }
              >
                <img
                  src={getImageUrl(item.item_image, IMAGE_TYPE.ITEM)}
                  onError={onImageError}
                  alt={item.name}
                />
                <div>{item.name}</div>
              </Link>

              {page === "buy" && item.order_id && (
                <div className="mt-1">
                  <Link
                    href={`/mypage/orders/${item.order_id}`}
                    className="text-xs text-blue-600 underline"
                  >
                    配送状況を見る
                  </Link>
                </div>
              )}
            </div>
          ))
        )}
      </div>
    </div>
  );
}
