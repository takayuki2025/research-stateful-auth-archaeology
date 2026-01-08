"use client";

import { useMemo } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { mutate } from "swr";

import { useAuthGuard } from "@/ui/auth/useAuthGuard";
import { useAuth } from "@/ui/auth/useAuth";

import { useItemListSWR } from "@/services/useItemListSWR";
import { useItemSearchSWR } from "@/services/useItemSearchSWR";
import { useFavoriteItemsSWR } from "@/services/useFavoriteItemsSWR";

import type { PublicItemSummary } from "@/types/publicItemSummary";
import { getImageUrl, IMAGE_TYPE, onImageError } from "@/utils/utils";

import styles from "./W-Resource-Rich-Simulation-Center-Home.module.css";

export default function Home() {
  useAuthGuard();

  const router = useRouter();
  const searchParams = useSearchParams();
  const { isAuthenticated, isLoading: isAuthLoading, authClient } = useAuth();

  /* =========================
     Tab / Search
  ========================= */
  const currentTab = useMemo<"all" | "mylist">(
    () => (searchParams.get("tab") === "mylist" ? "mylist" : "all"),
    [searchParams]
  );

  const searchQuery = useMemo(
    () => searchParams.get("all_item_search") ?? "",
    [searchParams]
  );

  const isSearch = searchQuery.trim().length > 0;

  /* =========================
     Hooks（無条件）
  ========================= */
  const listResult = useItemListSWR();
  const searchResult = useItemSearchSWR(searchQuery);
  const favoriteResult = useFavoriteItemsSWR();

  const isItemsLoading =
    listResult.isLoading || searchResult.isLoading || favoriteResult.isLoading;

  /* =========================
     Normalize items
  ========================= */
  const items: PublicItemSummary[] = useMemo(() => {
    const raw =
      currentTab === "mylist"
        ? favoriteResult.items
        : isSearch
          ? searchResult.items
          : listResult.items;

    return raw.map((item) => ({
      id: item.id,
      name: item.name,
      price: item.price ?? null,
      itemImagePath: item.itemImagePath ?? null,
      displayType: item.displayType ?? null,
      isFavorited: Boolean(item.isFavorited),
    }));
  }, [
    currentTab,
    isSearch,
    listResult.items,
    searchResult.items,
    favoriteResult.items,
  ]);

  const isPageLoading = isAuthLoading || isItemsLoading;

  /* =========================
     Favorite toggle
  ========================= */
  const toggleFavorite = async (
    item: PublicItemSummary,
    isFavorited: boolean
  ) => {
    if (!authClient) return;

    try {
      if (isFavorited) {
        await authClient.delete(`/favorites/${item.id}`);
      } else {
        await authClient.post(`/favorites/${item.id}`);
      }
      mutate();
    } catch (e) {
      console.error(e);
    }
  };

  /* =========================
     Render
  ========================= */
  return (
    <div className={styles.main_contents}>
      {isPageLoading && (
        <div className={styles.loadingBox}>
          <div className={styles.spinner} />
          <p className={styles.loadingText}>読み込み中...</p>
        </div>
      )}

      {!isPageLoading && (
        <>
          {/* Tabs */}
          <div className={styles.main_select}>
            <Link
              href={{ pathname: "/", query: { all_item_search: searchQuery } }}
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
                        {item.displayType && (
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
