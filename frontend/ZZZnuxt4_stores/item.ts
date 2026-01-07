import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { $fetch } from "ofetch";
import { useApi } from "~/composables/useApi";

// -------------------------------------------------------------------------
// 型定義 (変更なし)
// -------------------------------------------------------------------------
interface User {
  id: number;
  name: string;
  user_image?: string;
}

interface Item {
  id: number;
  user_id: number;
  name: string;
  price: number;
  brand: string | null;
  explain: string;
  condition: string;
  category: string;
  item_image: string; // バックエンドから絶対URLが入る
  remain: number;
  user: User;
}

interface Comment {
  id: number;
  item_id: number;
  user_id: number;
  comment: string;
  created_at: string;
  user: User;
}

interface ItemDetailResponse {
  item: Item;
  comments: Comment[];
  is_favorited: boolean;
  favorites_count: number;
  userId: number | null;
  isLoggedIn: boolean;
}

// -------------------------------------------------------------------------

export const useItemStore = defineStore("item", () => {
  const config = useRuntimeConfig();
  const API_BASE_URL = config.public.apiBaseUrl;
  const { authenticatedFetch } = useApi();

  // 状態 (State - 変更なし)
  const items = ref<Item[]>([]);
  const item = ref<Item | null>(null);
  const comments = ref<Comment[]>([]);
  const isFavorited = ref(false);
  const favoritesCount = ref(0);
  const currentUserId = ref<number | null>(null);
  const isLoggedIn = ref(false);
  const isLoading = ref(false);
  const errors = ref<string[]>([]);

  // ゲッター (Getters - 変更なし)
  const isSeller = computed(() =>
    item.value ? item.value.user_id === currentUserId.value : false
  );
  const isSold = computed(() => (item.value ? item.value.remain < 1 : true));

  const displayPrice = computed(() => {
    if (!item.value) return "---";
    if (item.value.remain === 0) {
      return "SOLD";
    }
    return item.value.price.toLocaleString();
  });

  const parsedCategories = computed<string[]>(() => {
    if (!item.value || !item.value.category) return [];
    try {
      // APIから返される category はJSON文字列と仮定
      const categories = JSON.parse(item.value.category as string);
      return Array.isArray(categories) ? categories : [];
    } catch (e) {
      console.warn("カテゴリのパースに失敗しました:", e);
      return [];
    }
  });

  function clearData() {
    items.value = [];
    item.value = null;
    comments.value = [];
    isFavorited.value = false;
    favoritesCount.value = 0;
    currentUserId.value = null;
    isLoggedIn.value = false;
    isLoading.value = false;
    errors.value = [];
    console.log("[ItemStore] State manually cleared for logout.");
  }

  /**
   * 💡 商品画像やユーザー画像はバックエンドのアクセサで絶対URLに変換されていることを期待し、
   * ここでは**いかなる加工も行いません**。
   */

  // アクション (Actions: API通信)

  async function fetchItems(
    token: string | null,
    query: string = "",
    tab: "all" | "mylist" = "all"
  ) {
    isLoading.value = true;
    errors.value = [];
    items.value = [];

    if (tab === "mylist") {
      errors.value = ["「マイリスト」タブの機能は未実装です。"];
      isLoading.value = false;
      return;
    }

    try {
      const url = new URL(`${API_BASE_URL}/items`);

      if (query && query.trim()) {
        url.searchParams.append("q", query.trim());
      }

      const data = await $fetch(url.toString(), {
        method: "GET",
      });

      const responseData = data as { items: Item[] };
      if (responseData && Array.isArray(responseData.items)) {
        // 💡 修正: item_imageはバックエンドから絶対URLとして返されるため、そのまま使用します。
        items.value = responseData.items.map((item) => ({
          ...item,
          item_image: item.item_image, // 絶対URLをそのまま使用
          // ユーザー画像もそのまま使用（バックエンドに任せる）
          user: {
            ...item.user,
            user_image: item.user.user_image,
          },
        }));
      } else {
        throw new Error("商品リストのデータ構造が不正です。");
      }
    } catch (e: any) {
      console.error("商品リストの取得に失敗:", e);
      let errorMessage = "商品リストの取得中にエラーが発生しました。";
      if (e.message) {
        errorMessage = e.message;
      }
      errors.value = [errorMessage];
      items.value = [];
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * 商品詳細データ、コメント、お気に入り状態をAPIから取得する
   */
  async function fetchItemDetail(itemId: number, token: string | null) {
    if (typeof itemId !== "number" || isNaN(itemId) || itemId <= 0) {
      errors.value = ["商品IDが無効です。ID取得を確認してください。"];
      isLoading.value = false;
      console.error(`[ItemStore:fetchItemDetail] Invalid itemId: ${itemId}`);
      return;
    }

    isLoading.value = true;
    errors.value = [];
    item.value = null;
    comments.value = null as unknown as Comment[];
    console.log(
      `[ItemStore:fetchItemDetail] Starting fetch for item ID: ${itemId}`
    );

    try {
      const responseData = (await authenticatedFetch(`/items/${itemId}`, {
        method: "GET",
      })) as ItemDetailResponse;

      // 💡 修正: バックエンドから返された絶対URLをそのまま item_image に格納します。
      item.value = {
        ...responseData.item,
        item_image: responseData.item.item_image, // 絶対URLをそのまま使用
      };

      // 💡 ユーザーの画像パスもそのまま使用
      if (item.value.user && item.value.user.user_image) {
        item.value.user.user_image = item.value.user.user_image;
      }
      // 💡 コメントユーザーの画像パスもそのまま使用
      comments.value = responseData.comments.map((comment) => ({
        ...comment,
        user: {
          ...comment.user,
          user_image: comment.user.user_image,
        },
      }));

      isFavorited.value = responseData.is_favorited;
      favoritesCount.value = responseData.favorites_count;
      currentUserId.value = responseData.userId;
      isLoggedIn.value = responseData.isLoggedIn;

      // 💡 デバッグログ (Item Image URLが正しい絶対URLになっているかを確認)
      console.log("--- Debug Item Store (After authenticatedFetch) ---");
      console.log(
        `API is_favorited: ${responseData.is_favorited} -> Store isFavorited: ${isFavorited.value}`
      );
      console.log(`Favorites Count: ${responseData.favorites_count}`);
      console.log(
        `Current User ID: ${currentUserId.value}, Is Logged In: ${isLoggedIn.value}`
      );
      if (item.value) {
        // !!! このログが 'https://laravel.test:4430/storage/item_images/...' と表示されれば成功です !!!
        console.log(`Item Image URL (Absolute): ${item.value.item_image}`);
      }
      console.log(`Number of comments loaded: ${comments.value.length}`);

      if (!item.value) {
        throw new Error("商品詳細データが空です。");
      }
    } catch (e: any) {
      console.error("商品詳細の取得に失敗:", e);
      let errorMessage = "データの取得中に予期せぬエラーが発生しました。";
      if (e.message) {
        errorMessage = e.message;
      }
      errors.value = [errorMessage];
      item.value = null;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * 💡 統合: お気に入り状態をトグルする (変更なし)
   */
  async function toggleFavorite(token: string | null) {
    if (!token || !item.value) {
      errors.value = ["お気に入りに登録/解除するにはログインが必要です。"];
      return;
    }

    errors.value = [];
    const currentStatus = isFavorited.value;

    // 楽観的更新
    isFavorited.value = !currentStatus;
    favoritesCount.value += isFavorited.value ? 1 : -1;

    try {
      const url = `/items/${item.value.id}/favorite`;

      const responseData = await authenticatedFetch(url, {
        method: "POST",
      });

      // APIからのJSONレスポンスを処理し、ストアの状態をAPIの戻り値で更新
      isFavorited.value = responseData.is_favorited as boolean;
      favoritesCount.value = responseData.favorites_count as number;
    } catch (e: any) {
      console.error("お気に入り操作に失敗:", e);
      let errorMessage =
        "お気に入り操作に失敗しました。認証状態またはネットワークを確認してください。";

      if (e.status === 401) {
        errorMessage = "認証が必要です。ログインしてください。";
      }

      errors.value = [errorMessage];
      // 悲観的ロールバック
      isFavorited.value = currentStatus;
      favoritesCount.value -= isFavorited.value ? 1 : -1;
    }
  }

  /**
   * 💡 統合: コメントを投稿する
   */
  async function postComment(commentText: string, token: string | null) {
    if (!token || !item.value) {
      errors.value = ["コメントを投稿するにはログインが必要です。"];
      return;
    }

    errors.value = [];

    try {
      const responseData = await authenticatedFetch(`/comment`, {
        method: "POST",
        body: {
          item_id: item.value.id,
          comment: commentText,
        },
      });

      const newCommentData = responseData as Comment;
      if (newCommentData && newCommentData.id) {
        // 新規コメントの画像パスもそのまま使用
        if (newCommentData.user && newCommentData.user.user_image) {
          newCommentData.user.user_image = newCommentData.user.user_image;
        }
        comments.value.unshift(newCommentData);
      } else {
        // サーバーが新しいコメントを返さなかった場合は、手動でコメント一覧を再取得する
        await fetchItemDetail(item.value.id, token);
      }
    } catch (e: any) {
      console.error("コメント投稿に失敗:", e);
      let errorMessage = "コメントの投稿に失敗しました。";
      if (e.status === 401) {
        errorMessage = "コメント投稿には認証が必要です。";
      } else if (e.response && e.response._data && e.response._data.message) {
        errorMessage = e.response._data.message;
      }
      errors.value = [errorMessage];
    }
  }

  return {
    item,
    items,
    comments,
    isFavorited,
    favoritesCount,
    isLoggedIn,
    isLoading,
    errors,
    currentUserId,
    isSeller,
    isSold,
    displayPrice,
    parsedCategories,
    fetchItemDetail,
    toggleFavorite,
    postComment,
    clearData,
    fetchItems,
  };
});
