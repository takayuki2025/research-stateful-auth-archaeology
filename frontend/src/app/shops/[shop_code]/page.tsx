"use client";

import React, { useMemo } from "react";
import Link from "next/link";
import { useParams, useSearchParams } from "next/navigation";

import { useItemListByShopSWR } from "@/services/useItemListByShopSWR";
import { useItemSearchByShopSWR } from "@/services/useItemSearchByShopSWR";
import { useAuth } from "@/ui/auth/useAuth";
import type { ShopRole } from "@/types/auth";
import type { Item } from "@/types/item";
import { getImageUrl } from "@/utils/utils";
import styles from "./W-Shop-Home.module.css";

export default function ShopHomePage() {
  const { shop_code } = useParams<{ shop_code: string }>();
  const searchParams = useSearchParams();
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();

  /* =========================
     🔍 検索状態
  ========================= */
  const currentSearchQuery = useMemo(
    () => searchParams.get("q") || "",
    [searchParams],
  );
  const isSearch = currentSearchQuery.trim().length > 0;

  /* =========================
     📦 商品取得
  ========================= */
  const listResult = useItemListByShopSWR(shop_code);
  const searchResult = useItemSearchByShopSWR(shop_code, currentSearchQuery);

  const items: Item[] = isSearch ? searchResult.items : listResult.items;

  const isPageLoading =
    authLoading || (isSearch ? searchResult.isLoading : listResult.isLoading);

  /* =========================
     🔐 このショップのスタッフか？
  ========================= */
  // type Old = { shop_id: number };
  // type New = { shop_id: number; shop_code: string; role: string };
  const isShopStaff = useMemo(() => {
    if (!isAuthenticated || !user?.shop_roles) return false;

    return user.shop_roles.some(
      (r) =>
        r.shop_code === shop_code &&
        ["owner", "manager", "staff"].includes(r.role),
    );
  }, [isAuthenticated, user, shop_code]);

  /* =========================
     ⏳ Loading
  ========================= */
  if (isPageLoading) {
    return <div className={styles.loadingBox}>読み込み中...</div>;
  }

  /* =========================
     🎨 UI
  ========================= */
  return (
    <div className={styles.main_contents}>
      {/* ===== ヘッダー ===== */}
      <div className={styles.shopHeader}>
        <h1 className={styles.title}>Shop: {shop_code}</h1>

        {isShopStaff && (
          <Link
            href={`/shops/${shop_code}/dashboard`}
            className={styles.dashboardButton}
          >
            管理画面
          </Link>
        )}
      </div>

      {/* ===== 商品一覧 ===== */}
      <div className={styles.items_select}>
        {items.map((item) => (
          <div key={item.id} className={styles.items_select_all}>
            <Link href={`/item/${item.id}`} className={styles.cardLink}>
              <img src={getImageUrl(item.item_image)} alt={item.name} />
              <div>
                <p>{item.name}</p>
                <p>¥{item.price?.toLocaleString()}</p>
              </div>
            </Link>
          </div>
        ))}
      </div>

      {!isAuthenticated && (
        <div className={styles.notice}>
          ログインすると購入やマイリストが使えます。
        </div>
      )}
    </div>
  );
}
