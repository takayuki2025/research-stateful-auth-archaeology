"use client";

import React, { useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";

import { useAuth } from "@/ui/auth/AuthProvider";
import { useItemDetailSWR } from "@/services/useItemDetailSWR";
import { useFavoriteItemsSWR } from "@/services/useFavoriteItemsSWR";
// import type {
//   ItemDetailResponse,
//   ItemDisplayAttribute,
// } from "@/services/useItemDetailSWR";
import { getImageUrl, IMAGE_TYPE, onImageError } from "@/utils/utils";

import styles from "./W-ItemDetailView.module.css";

/* =========================
   util
========================= */
// function toTokenList(input: unknown): string[] {
//   if (!input) return [];
//   if (Array.isArray(input)) {
//     return input.map((v) => String(v ?? "").trim()).filter(Boolean);
//   }
//   const s = String(input).trim();
//   if (!s) return [];
//   return s
//     .split(/[|/,\u3001\u30fb]+/)
//     .map((v) => v.trim())
//     .filter(Boolean);
// }

function shortenLabel(s: string, max = 14): string {
  const t = s.trim();
  return t.length <= max ? t : t.slice(0, max) + "…";
}

/* =========================
   Loading UI
========================= */
function ItemDetailLoading() {
  return (
    <div className={styles.loadingWrapper}>
      {/* 上段：スピナー＋メインメッセージ */}
      <div className={styles.loadingMain}>
        <div className={styles.spinner} />
        <p className={styles.loadingText}>商品情報を読み込み中...</p>
      </div>

      {/* 下段：補足説明 */}
      <p className={styles.loadingSubText}>
        解析された商品情報を取得しています
      </p>
    </div>
  );
}

export default function ItemDetailPage() {
  const params = useParams();
  const router = useRouter();
  const auth = useAuth();

  const [isTogglingFavorite, setIsTogglingFavorite] = useState(false);

  /* =========================
     itemId
  ========================= */
  const itemId = useMemo(() => {
    const raw = (params as any).items_id;
    if (!raw) return null;
    const id = Array.isArray(raw) ? raw[0] : raw;
    const n = Number(id);
    return Number.isNaN(n) ? null : n;
  }, [params]);

  /* =========================
     SWR（読むだけ）
  ========================= */
  const {
    item,
    comments,
    isFavorited,
    favoritesCount,
    isLoading,
    isError,
    mutateItemDetail,
  } = useItemDetailSWR(itemId);

  /* =========================
     local state
  ========================= */
  const [newComment, setNewComment] = useState("");
  const [commentErrors, setCommentErrors] = useState<string[]>([]);
  const [isSubmittingComment, setIsSubmittingComment] = useState(false);

  const isAuthenticated = auth.isAuthenticated;
  // const user = auth.user;
  const { refetchFavorites } = useFavoriteItemsSWR();

  /* =========================================================
     🛑 Guard（UX 改善の核心）
  ========================================================= */

  // ✅ 通信中 or item がまだ確定していない（正常系）
  if (isLoading || (!isError && !item)) {
    return <ItemDetailLoading />;
  }

  // ❌ 明確なエラーのみ
  if (isError) {
    return (
      <div className={styles.errorBox}>
        <p className={styles.errorTitle}>商品情報の取得に失敗しました</p>
        <p>時間をおいて再度お試しください。</p>
      </div>
    );
  }

  if (!item) {
    return <ItemDetailLoading />;
  }

  // ✅ ここから下は「必ず item が存在する」ので確定変数に寄せる
  const resolvedItem = item;


  
  /* =========================
     ここから下は item が必ず存在
  ========================= */

  const isOwner = false;
  const canInteract = isAuthenticated && !isOwner;
  const isSoldOut = resolvedItem.remain === 0;

  const displayedFavorited = isFavorited;
  const displayedCount = favoritesCount;

  /* =========================
     ❤️ Favorite（唯一ここだけ mutate）
  ========================= */
  const submitFavorite = async (e: React.MouseEvent<HTMLButtonElement>) => {
    e.preventDefault();
    e.stopPropagation();

    if (!isAuthenticated || !auth.apiClient) {
      router.push("/login");
      return;
    }
    if (isTogglingFavorite) return;

    setIsTogglingFavorite(true);

    let nextFavorited: boolean | null = null;

    // optimistic update
    mutateItemDetail(
      (current) => {
        if (!current) return current;

        nextFavorited = !current.is_favorited;

        return {
          ...current,
          is_favorited: nextFavorited,
          favorites_count: Math.max(
            0,
            current.favorites_count + (nextFavorited ? 1 : -1)
          ),
        };
      },
      { revalidate: false }
    );

    try {
      if (nextFavorited) {
        await auth.apiClient.post(
          `/reactions/items/${resolvedItem.id}/favorite`
        );
      } else {
        await auth.apiClient.delete(
          `/reactions/items/${resolvedItem.id}/favorite`
        );
      }

      refetchFavorites();
    } catch {
      mutateItemDetail();
    } finally {
      setIsTogglingFavorite(false);
    }
  };

  /* =========================
     💬 Comment
  ========================= */
  const submitComment = async () => {
    // ✅ resolvedItem を使うことで "item は null の可能性" を根絶
    if (!newComment.trim()) {
      setCommentErrors(["コメントを入力してください"]);
      return;
    }

    if (!isAuthenticated || !auth.apiClient) {
      router.push("/login");
      return;
    }

    setIsSubmittingComment(true);
    setCommentErrors([]);

    try {
      await auth.apiClient.post("/comment", {
        item_id: resolvedItem.id,
        comment: newComment,
      });

      setNewComment("");
      mutateItemDetail();
    } catch {
      setCommentErrors(["コメント投稿に失敗しました"]);
    } finally {
      setIsSubmittingComment(false);
    }
  };

  // ブランド（AI解析 → 人手入力 fallback）
  const brandTokens: string[] = resolvedItem.display?.brand?.name
    ? [resolvedItem.display.brand.name]
    : resolvedItem.brand
      ? [resolvedItem.brand]
      : [];

  // 状態
  const rawCondition: string | null =
    resolvedItem.display?.condition?.name ?? resolvedItem.condition ?? null;

  // カラー
  const rawColor: string | null =
    resolvedItem.display?.color?.name ?? resolvedItem.color ?? null;

  const categoryTokens: string[] = Array.isArray(resolvedItem.category)
    ? resolvedItem.category
    : resolvedItem.category
      ? [resolvedItem.category]
      : [];

  const navigateToPurchase = () => {
    router.push(`/purchase/${resolvedItem.id}`);
  };

type DisplayBrand = {
  name: string | null;
  source?: "ai_provisional" | "human_confirmed";
  is_latest?: boolean; // 後方互換
};

const displayBrand = (resolvedItem.display?.brand ??null) as DisplayBrand | null;

const badge =
  displayBrand?.is_latest && displayBrand?.source === "human_confirmed" ? (
    <span
      style={{
        color: "#22c55e", // 濃いめの緑（確定済み）
        fontSize: "0.90rem",
        lineHeight: "1.4",
        display: "inline-block",
        marginLeft: "40px",
        verticalAlign: "middle",
      }}
    >
      AI解析 → 管理手動確定
      <br />
      （ブランド名・カラー・コンディション、
      <br />
      開発計画中:画像解析など）
    </span>
  ) : displayBrand?.source === "ai_provisional" ? (
    <span
      style={{
        color: "#a3e635", // 黄緑色 (Tailwindのlime-400相当)
        fontSize: "0.90rem",
        display: "inline-block",
        marginLeft: "40px", // 位置を同じに設定
        verticalAlign: "middle",
      }}
    >
      AI解析
      <br />
      （ブランド名・カラー・コンディション、
      <br />
      開発計画中:画像解析など）
    </span>
  ) : null;

  /* =========================
    JSX
  ========================= */
  return (
    <div className={styles.item_detail_wrapper}>
      <div className={styles.item_detail_contents}>
        <div className={styles.card}>
          {/* 商品画像エリア */}
          <div className={styles.imageArea}>
            <img
              src={getImageUrl(resolvedItem.item_image)}
              onError={(e) => onImageError(e, resolvedItem.name)}
              alt="商品写真"
              className={styles.image}
            />
          </div>

          {/* 商品情報エリア */}
          <div className={styles.infoArea}>
            <h2 className={styles.itemTitle}>{resolvedItem.name}</h2>
            {badge && <span className={styles.aiBadge}>{badge}</span>}
            {/* ブランド */}
            <div className={styles.brandBlock}>
              <p className={styles.brandLabel}>ブランド名</p>
              <div className={styles.brandTokensRow}>
                {brandTokens.length > 0 ? (
                  brandTokens.map((b, idx) => (
                    <button key={idx} className={styles.brandToken}>
                      {shortenLabel(b)}
                    </button>
                  ))
                ) : (
                  <p className={styles.brandValue}>未登録</p>
                )}
              </div>
            </div>

            {/* 価格 */}
            <div className={styles.priceBlock}>
              {isSoldOut ? (
                <h2 className={styles.priceSoldOut}>SOLD OUT</h2>
              ) : (
                <h2 className={styles.price}>
                  ¥{resolvedItem.price?.toLocaleString()}
                  <span className={styles.priceAfter}> (税込)</span>
                </h2>
              )}
            </div>

            {/* お気に入り */}
            <div className={styles.reactionRow}>
              <div className={styles.favoriteBlock}>
                {canInteract ? (
                  <button
                    type="button"
                    className={styles.favoriteBtn}
                    onClick={submitFavorite}
                  >
                    <span
                      className={`${styles.favoriteIcon} ${
                        displayedFavorited ? styles.favoriteActive : ""
                      }`}
                    >
                      {displayedFavorited ? "❤️" : "🤍"}
                    </span>
                  </button>
                ) : (
                  <span className={styles.disabledHeart}>🤍</span>
                )}
                <p className={styles.favoriteCount}>{displayedCount}</p>
              </div>
            </div>

            {/* 購入ボタン */}
            <div className="item_detail_form pt-4">
              <button
                type="button" // ★ 必須
                onClick={() => {
                  if (isOwner) {
                    router.push("/mypage");
                  } else if (!isAuthenticated) {
                    router.push("/login");
                  } else {
                    navigateToPurchase();
                  }
                }}
                disabled={(isSoldOut && !isOwner) || isLoading}
                className={`w-full py-3 text-lg font-bold rounded-lg transition duration-200 shadow-lg ${
                  !isSoldOut
                    ? "bg-red-600 text-white hover:bg-red-700 active:bg-red-800"
                    : "bg-gray-400 text-gray-700 cursor-not-allowed"
                }`}
              >
                {isOwner
                  ? "マイページへ移動する"
                  : !isAuthenticated
                    ? "ログインして購入"
                    : isSoldOut
                      ? "SOLD OUT"
                      : "カートへ"}
              </button>
            </div>

            {/* 商品説明 */}
            <div className={styles.section}>
              <h2 className={styles.sectionTitle}>商品説明</h2>
              <p className={styles.explainText}>{resolvedItem.explain}</p>
            </div>

            {/* 商品情報 */}
            <div className={styles.section}>
              <h2 className={styles.sectionTitle}>商品情報</h2>

              <div className={styles.categoryRow}>
                <p className={styles.categoryLabel}>カテゴリー：</p>
                <ul className={styles.categoryList}>
                  {categoryTokens.map((c) => (
                    <li key={c}>{c}</li>
                  ))}
                </ul>
              </div>

              {/* 状態：左 raw / 右 加工後（スペースあり） */}
              <div
                className={styles.conditionRow}
                style={{ display: "flex", gap: 14 }}
              >
                <div style={{ flex: 1, minWidth: 0 }}>
                  <p className={styles.conditionLabel}>商品の状態：</p>
                  <p className={styles.conditionValue}>
                    {rawCondition || "未登録"}
                  </p>
                </div>

                {/* <div style={{ flex: 1, minWidth: 0 }}>
                  <p className={styles.conditionLabel}>Update</p>
                  <p className={styles.conditionValue}>
                    {displayCondition || rawCondition || "未登録"}
                  </p>
                </div>
              </div> */}

                {/* カラー：新規追加 */}
                <div className={styles.conditionRow} style={{ marginTop: 10 }}>
                  <p className={styles.conditionLabel}>カラー：</p>
                  <div className={styles.conditionValue}>
                    {rawColor || "未登録"}
                  </div>
                </div>
              </div>

              {/* コメント一覧 */}
              <div className={styles.section}>
                <div className={styles.commentHeader}>
                  <h2 className={styles.sectionTitle}>コメント</h2>
                  <span className={styles.commentCountText}>
                    ({comments.length})
                  </span>
                </div>

                {comments.length > 0 ? (
                  <div className={styles.commentList}>
                    {comments.map((comment) => (
                      <div key={comment.id} className={styles.commentItem}>
                        <div className={styles.commentUserRow}>
                          <img
                            src={getImageUrl(
                              comment.user.user_image,
                              IMAGE_TYPE.USER
                            )}
                            className={styles.commentUserImage}
                            onError={onImageError}
                          />
                          <p className={styles.commentUserName}>
                            {comment.user.name}
                          </p>
                        </div>

                        <p className={styles.commentText}>{comment.comment}</p>

                        <small className={styles.commentDate}>
                          投稿日時:{" "}
                          {new Date(comment.created_at).toLocaleString()}
                        </small>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className={styles.noComments}>
                    まだコメントはありません。
                  </p>
                )}
              </div>

              {/* コメント投稿 */}
              <div className={styles.section}>
                <h2 className={styles.sectionTitle}>商品へのコメント</h2>

                {commentErrors.length > 0 && (
                  <div className={styles.errorBoxSmall}>
                    {commentErrors.map((err, index) => (
                      <p key={index}>{err}</p>
                    ))}
                  </div>
                )}

                {isAuthenticated ? (
                  <>
                    <textarea
                      value={newComment}
                      onChange={(e) => setNewComment(e.target.value)}
                      rows={5}
                      className={styles.textarea}
                    />

                    <button
                      type="button" // ★ 必須
                      className={styles.submitBtn}
                      onClick={submitComment}
                      disabled={isSubmittingComment}
                    >
                      {isSubmittingComment ? "投稿中..." : "コメントを送信する"}
                    </button>
                  </>
                ) : (
                  <p
                    className={styles.submitBtn}
                    onClick={() => router.push("/login")}
                    style={{ cursor: "pointer" }}
                  >
                    ログインしてコメントする
                  </p>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
