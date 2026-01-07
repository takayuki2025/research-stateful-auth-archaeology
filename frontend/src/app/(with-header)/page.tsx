"use client";

import React, { useMemo, useEffect, useState } from "react";
import Link from "next/link";
import { mutate } from "swr";
import { useSearchParams, useRouter } from "next/navigation";

import { useItemListSWR } from "@/services/useItemListSWR";
import { useItemSearchSWR } from "@/services/useItemSearchSWR";
import { useFavoriteItemsSWR } from "@/services/useFavoriteItemsSWR";

import type { PublicItem } from "@/types/publicItem";
import { getImageUrl, IMAGE_TYPE, onImageError } from "@/utils/utils";
import { useAuth } from "@/ui/auth/useAuth";
import styles from "./W-Resource-Rich-Simulation-Center-Home.module.css";

export default function Home() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { isAuthenticated, isLoading: isAuthLoading, authClient } = useAuth();

  /* =========================
     🔐 Profile Gate
  ========================= */
  const [profileChecked, setProfileChecked] = useState(false);
  const [hasProfile, setHasProfile] = useState<boolean | null>(null);

  useEffect(() => {
    if (!isAuthenticated) return;

    let cancelled = false;

    (async () => {
      try {
        const data = await authClient.get("/mypage/profile");
        if (cancelled) return;
        setHasProfile(!!data?.has_profile);
        setProfileChecked(true);
      } catch {
        setHasProfile(false);
        setProfileChecked(true);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [isAuthenticated, authClient]);

  useEffect(() => {
    if (isAuthenticated && profileChecked && hasProfile === false) {
      router.replace("/mypage/profile");
    }
  }, [isAuthenticated, profileChecked, hasProfile, router]);

  /* =========================
     🔖 Tab / Search
  ========================= */
  const currentTab = useMemo(
    () => (searchParams.get("tab") === "mylist" ? "mylist" : "all"),
    [searchParams]
  );

  const currentSearchQuery = useMemo(
    () => searchParams.get("all_item_search") || "",
    [searchParams]
  );

  const isSearch = currentSearchQuery.trim().length > 0;

  /* =========================
     📦 Data Hooks
  ========================= */
  const listResult = useItemListSWR();
  const searchResult = useItemSearchSWR(currentSearchQuery);
  const favoriteResult = useFavoriteItemsSWR();

  const isItemsLoading =
    currentTab === "mylist"
      ? favoriteResult.isLoading
      : isSearch
        ? searchResult.isLoading
        : listResult.isLoading;

  const items: PublicItem[] = useMemo(() => {
    const raw =
      currentTab === "mylist"
        ? favoriteResult.items
        : isSearch
          ? searchResult.items
          : listResult.items;

    return raw.map((item: any) => ({
      id: item.id,
      name: item.name,
      price: isSearch ? item.price.amount : item.price,
      itemImagePath: isSearch
        ? null
        : (item.itemImagePath ?? item.item_image ?? null),
      displayType: item.displayType ?? null,
      isFavorited: item.isFavorited ?? false,
    }));
  }, [
    currentTab,
    isSearch,
    favoriteResult.items,
    searchResult.items,
    listResult.items,
  ]);

  const isGateLoading =
    isAuthenticated && (!profileChecked || hasProfile === null);

  const isPageLoading = isAuthLoading || isItemsLoading || isGateLoading;

  /* =========================
     ❤️ Favorite
  ========================= */
  const toggleFavorite = async (item: PublicItem, isFavorited: boolean) => {
    try {
      if (isFavorited) {
        await authClient.delete(`/reactions/items/${item.id}/favorite`);
      } else {
        await authClient.post(`/reactions/items/${item.id}/favorite`);
      }
      mutate("/items/favorite");
      await favoriteResult.refetchFavorites();
    } catch (e) {
      console.error(e);
    }
  };
console.log("HOME RENDERED");
  /* =========================
     🎨 Render
  ========================= */
  if (isGateLoading) {
    return (
      <div className={styles.main_contents}>
        <div className={styles.loadingBox}>
          <div className={styles.spinner}></div>
          <p className={styles.loadingText}>確認中...</p>
        </div>
      </div>
    );
  }

  return (
    <div className={styles.main_contents}>
      {isPageLoading && (
        <div className={styles.loadingBox}>
          <div className={styles.spinner}></div>
          <p className={styles.loadingText}>読み込み中...</p>
        </div>
      )}

      {!isPageLoading && (
        <>
          {/* 🏪 テスト用ショップリンク */}
          <div className={styles.shopButtons}>
            {["a", "b", "c", "d"].map((code) => (
              <button
                key={code}
                onClick={() => router.push(`/shops/shop-${code}`)}
                className={styles.shopButton}
              >
                テストリンク ショップ{code.toUpperCase()}
              </button>
            ))}
          </div>

          {/* Tabs */}
          <div className={styles.main_select}>
            <Link
              href={{
                pathname: "/",
                query: { tab: "all", all_item_search: currentSearchQuery },
              }}
              className={`${styles.recs} ${
                currentTab === "all" ? styles.active : ""
              }`}
            >
              すべて
            </Link>

            <Link
              href={{ pathname: "/", query: { tab: "mylist" } }}
              className={`${styles.mylists} ${
                currentTab === "mylist" ? styles.active : ""
              }`}
            >
              マイリスト
            </Link>
          </div>

          {/* Items */}
          <div className={styles.items_select}>
            {items.length > 0 ? (
              items.map((item) => {
                const isFavorited = item.isFavorited === true;

                return (
                  <div key={item.id} className={styles.items_select_all}>
                    <div
                      className={styles.cardLink}
                      role="button"
                      tabIndex={0}
                      onClick={() => router.push(`/item/${item.id}`)}
                    >
                      <div className={styles.itemImageWrapper}>
                        {item.displayType &&
                          item.displayType !== "FAVORITE" && (
                            <span className={styles.ownStar}>
                              {item.displayType === "STAR" ? "⭐️" : "💫"}
                            </span>
                          )}

                        {isAuthenticated && (
                          <button
                            className={styles.favoriteButton}
                            onClick={(e) => {
                              e.stopPropagation();
                              toggleFavorite(item, isFavorited);
                            }}
                          >
                            {isFavorited ? "❤️" : "🤍"}
                          </button>
                        )}

                        <img
                          src={getImageUrl(item.itemImagePath, IMAGE_TYPE.ITEM)}
                          alt={item.name}
                          className={styles.itemImage}
                          onError={onImageError}
                        />
                      </div>

                      <div className={styles.item_info}>
                        <p className={styles.item_name}>{item.name}</p>
                        <p className={styles.item_price}>
                          ¥
                          {typeof item.price === "number"
                            ? item.price.toLocaleString()
                            : "-"}
                        </p>
                      </div>
                    </div>
                  </div>
                );
              })
            ) : (
              <div className={styles.no_items}>
                {currentTab === "mylist" && !isAuthenticated
                  ? "マイリストを見るにはログインが必要です。"
                  : "該当する商品が見つかりませんでした。"}
              </div>
            )}
          </div>
        </>
      )}
    </div>
  );
}