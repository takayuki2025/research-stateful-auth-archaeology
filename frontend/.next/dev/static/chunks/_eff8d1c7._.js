(globalThis.TURBOPACK || (globalThis.TURBOPACK = [])).push([typeof document === "object" ? document.currentScript : undefined,
"[project]/src/services/useItemDetailSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemDetailSWR",
    ()=>useItemDetailSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
;
const useItemDetailSWR = (itemId)=>{
    _s();
    const { apiClient, isAuthenticated, isReady } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    /**
   * fetch 条件
   */ const shouldFetch = typeof itemId === "number" && Number.isFinite(itemId) && isReady;
    /**
   * ✅ SWR Key（これが最重要）
   * auth / guest を分けない
   */ const swrKey = shouldFetch ? [
        "item-detail",
        itemId
    ] : null;
    /**
   * fetcher
   */ const fetcher = async ()=>{
        if (!itemId) {
            throw new Error("itemId is not available");
        }
        // 認証済み（JWT / Cookie）
        if (apiClient) {
            const res = await apiClient.get(`/items/${itemId}`);
            return res.data;
        }
        // 未ログイン（guest）
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"].get(`/api/items/${itemId}`);
        return res.data;
    };
    /**
   * SWR 本体
   */ const { data, error, isLoading, mutate } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(swrKey, fetcher, {
        // ❌ 勝手に戻る原因になる挙動は全て OFF
        revalidateOnFocus: false,
        revalidateOnReconnect: false,
        revalidateIfStale: false,
        shouldRetryOnError: false
    });
    /**
   * View 用に正規化して返す
   */ return {
        // core
        item: data?.item ?? null,
        comments: data?.comments ?? [],
        // ❤️ reaction
        isFavorited: data?.is_favorited ?? false,
        favoritesCount: data?.favorites_count ?? 0,
        // state
        isLoading,
        isError: error,
        /**
     * ✅ 外部から使える mutate
     * submitFavorite / rollback / 再検証 用
     */ mutateItemDetail: mutate
    };
};
_s(useItemDetailSWR, "6Nttq3U3oaPfTbqtdfVAykovBTs=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"],
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"]
    ];
});
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/utils/utils.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

// ======================================
// IMAGE TYPE（fallback 用）
// ======================================
__turbopack_context__.s([
    "IMAGE_TYPE",
    ()=>IMAGE_TYPE,
    "getImageUrl",
    ()=>getImageUrl,
    "onImageError",
    ()=>onImageError
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$build$2f$polyfills$2f$process$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = /*#__PURE__*/ __turbopack_context__.i("[project]/node_modules/next/dist/build/polyfills/process.js [app-client] (ecmascript)");
var IMAGE_TYPE = /*#__PURE__*/ function(IMAGE_TYPE) {
    IMAGE_TYPE["USER"] = "user";
    IMAGE_TYPE["ITEM"] = "item";
    IMAGE_TYPE["OTHER"] = "other";
    return IMAGE_TYPE;
}({});
// ======================================
// Backend Base URL
// ======================================
const BACKEND_BASE_URL = __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$build$2f$polyfills$2f$process$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"].env.NEXT_PUBLIC_BACKEND_URL ?? "https://localhost";
// ======================================
// Fallback Images（実在パス）
// ======================================
const DEFAULT_USER_IMAGE = `${BACKEND_BASE_URL}/pictures_user/default-profile2.jpg`;
const DEFAULT_ITEM_IMAGE = `${BACKEND_BASE_URL}/storage/pictures/no-image.png`;
function getImageUrl(path, type = "other") {
    // --- 未設定 ---
    if (!path) {
        if (type === "user") return DEFAULT_USER_IMAGE;
        return DEFAULT_ITEM_IMAGE;
    }
    // --- 完全 URL ---
    if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
    }
    // --- /storage から始まる ---
    if (path.startsWith("/storage/")) {
        return `${BACKEND_BASE_URL}${path}`;
    }
    // --- pictures_user（storage 不要） ---
    if (path.startsWith("pictures_user/")) {
        return `${BACKEND_BASE_URL}/${path}`;
    }
    // --- item_images / pictures は storage 配下 ---
    if (path.startsWith("item_images/") || path.startsWith("pictures/")) {
        return `${BACKEND_BASE_URL}/storage/${path}`;
    }
    // --- その他（保険） ---
    return `${BACKEND_BASE_URL}/${path}`;
}
const onImageError = (e)=>{
    const img = e.currentTarget;
    img.onerror = null;
    img.src = "https://placehold.co/300x300?text=No+Image";
};
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/app/item/[items_id]/W-ItemDetailView.module.css [app-client] (css module)", ((__turbopack_context__) => {

__turbopack_context__.v({
  "brandBlock": "W-ItemDetailView-module__tq4XVG__brandBlock",
  "brandLabel": "W-ItemDetailView-module__tq4XVG__brandLabel",
  "brandToken": "W-ItemDetailView-module__tq4XVG__brandToken",
  "brandTokensRow": "W-ItemDetailView-module__tq4XVG__brandTokensRow",
  "brandValue": "W-ItemDetailView-module__tq4XVG__brandValue",
  "card": "W-ItemDetailView-module__tq4XVG__card",
  "categoryLabel": "W-ItemDetailView-module__tq4XVG__categoryLabel",
  "categoryList": "W-ItemDetailView-module__tq4XVG__categoryList",
  "categoryRow": "W-ItemDetailView-module__tq4XVG__categoryRow",
  "categoryTag": "W-ItemDetailView-module__tq4XVG__categoryTag",
  "colorRow": "W-ItemDetailView-module__tq4XVG__colorRow",
  "commentCount": "W-ItemDetailView-module__tq4XVG__commentCount",
  "commentCountText": "W-ItemDetailView-module__tq4XVG__commentCountText",
  "commentDate": "W-ItemDetailView-module__tq4XVG__commentDate",
  "commentHeader": "W-ItemDetailView-module__tq4XVG__commentHeader",
  "commentIcon": "W-ItemDetailView-module__tq4XVG__commentIcon",
  "commentIconBlock": "W-ItemDetailView-module__tq4XVG__commentIconBlock",
  "commentItem": "W-ItemDetailView-module__tq4XVG__commentItem",
  "commentList": "W-ItemDetailView-module__tq4XVG__commentList",
  "commentText": "W-ItemDetailView-module__tq4XVG__commentText",
  "commentUserImage": "W-ItemDetailView-module__tq4XVG__commentUserImage",
  "commentUserName": "W-ItemDetailView-module__tq4XVG__commentUserName",
  "commentUserRow": "W-ItemDetailView-module__tq4XVG__commentUserRow",
  "conditionCol": "W-ItemDetailView-module__tq4XVG__conditionCol",
  "conditionLabel": "W-ItemDetailView-module__tq4XVG__conditionLabel",
  "conditionRow": "W-ItemDetailView-module__tq4XVG__conditionRow",
  "conditionValue": "W-ItemDetailView-module__tq4XVG__conditionValue",
  "errorBox": "W-ItemDetailView-module__tq4XVG__errorBox",
  "errorBoxSmall": "W-ItemDetailView-module__tq4XVG__errorBoxSmall",
  "errorTitle": "W-ItemDetailView-module__tq4XVG__errorTitle",
  "explainText": "W-ItemDetailView-module__tq4XVG__explainText",
  "favoriteActive": "W-ItemDetailView-module__tq4XVG__favoriteActive",
  "favoriteBlock": "W-ItemDetailView-module__tq4XVG__favoriteBlock",
  "favoriteBtn": "W-ItemDetailView-module__tq4XVG__favoriteBtn",
  "favoriteCount": "W-ItemDetailView-module__tq4XVG__favoriteCount",
  "favoriteIcon": "W-ItemDetailView-module__tq4XVG__favoriteIcon",
  "image": "W-ItemDetailView-module__tq4XVG__image",
  "imageArea": "W-ItemDetailView-module__tq4XVG__imageArea",
  "infoArea": "W-ItemDetailView-module__tq4XVG__infoArea",
  "itemTitle": "W-ItemDetailView-module__tq4XVG__itemTitle",
  "item_detail_contents": "W-ItemDetailView-module__tq4XVG__item_detail_contents",
  "item_detail_wrapper": "W-ItemDetailView-module__tq4XVG__item_detail_wrapper",
  "loadingText": "W-ItemDetailView-module__tq4XVG__loadingText",
  "loadingWrapper": "W-ItemDetailView-module__tq4XVG__loadingWrapper",
  "needLoginText": "W-ItemDetailView-module__tq4XVG__needLoginText",
  "noComments": "W-ItemDetailView-module__tq4XVG__noComments",
  "notFoundBox": "W-ItemDetailView-module__tq4XVG__notFoundBox",
  "notFoundText": "W-ItemDetailView-module__tq4XVG__notFoundText",
  "price": "W-ItemDetailView-module__tq4XVG__price",
  "priceAfter": "W-ItemDetailView-module__tq4XVG__priceAfter",
  "priceBlock": "W-ItemDetailView-module__tq4XVG__priceBlock",
  "priceSoldOut": "W-ItemDetailView-module__tq4XVG__priceSoldOut",
  "priceYen": "W-ItemDetailView-module__tq4XVG__priceYen",
  "reactionRow": "W-ItemDetailView-module__tq4XVG__reactionRow",
  "section": "W-ItemDetailView-module__tq4XVG__section",
  "sectionTitle": "W-ItemDetailView-module__tq4XVG__sectionTitle",
  "spin": "W-ItemDetailView-module__tq4XVG__spin",
  "spinner": "W-ItemDetailView-module__tq4XVG__spinner",
  "submitBtn": "W-ItemDetailView-module__tq4XVG__submitBtn",
  "textarea": "W-ItemDetailView-module__tq4XVG__textarea",
});
}),
"[project]/src/app/item/[items_id]/page.tsx [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ItemDetailPage
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/jsx-dev-runtime.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/index.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-client] (ecmascript) <export j as mutate>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemDetailSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemDetailSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/item/[items_id]/W-ItemDetailView.module.css [app-client] (css module)");
;
var _s = __turbopack_context__.k.signature();
"use client";
;
;
;
;
;
;
;
/* =========================
   util（変更なし）
========================= */ function toTokenList(input) {
    if (!input) return [];
    if (Array.isArray(input)) {
        return input.map((v)=>String(v ?? "").trim()).filter(Boolean);
    }
    const s = String(input).trim();
    if (!s) return [];
    return s.split(/[|/,\u3001\u30fb]+/).map((v)=>v.trim()).filter(Boolean);
}
function shortenLabel(s, max = 14) {
    const t = s.trim();
    return t.length <= max ? t : t.slice(0, max) + "…";
}
function ItemDetailPage() {
    _s();
    const params = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useParams"])();
    const router = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRouter"])();
    const auth = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    const [isTogglingFavorite, setIsTogglingFavorite] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])(false);
    /* =========================
     itemId
  ========================= */ const itemId = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "ItemDetailPage.useMemo[itemId]": ()=>{
            const raw = params.items_id;
            if (!raw) return null;
            const id = Array.isArray(raw) ? raw[0] : raw;
            const n = Number(id);
            return Number.isNaN(n) ? null : n;
        }
    }["ItemDetailPage.useMemo[itemId]"], [
        params
    ]);
    /* =========================
     SWR（初期取得のみ）
  ========================= */ const { item, comments, isFavorited, favoritesCount, isLoading, isError } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemDetailSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemDetailSWR"])(itemId);
    /* =========================
     ★ FIX: 表示の真実は local state
  ========================= */ const [newComment, setNewComment] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])("");
    const [commentErrors, setCommentErrors] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])([]);
    const [isSubmittingComment, setIsSubmittingComment] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])(false);
    const isAuthenticated = auth.isAuthenticated;
    const user = auth.user;
    /* =========================
     Guard
  ========================= */ if (isLoading) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].loadingText,
            children: "商品情報を読み込み中..."
        }, void 0, false, {
            fileName: "[project]/src/app/item/[items_id]/page.tsx",
            lineNumber: 72,
            columnNumber: 12
        }, this);
    }
    if (isError || !item) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].notFoundText,
            children: "商品が見つかりませんでした。"
        }, void 0, false, {
            fileName: "[project]/src/app/item/[items_id]/page.tsx",
            lineNumber: 76,
            columnNumber: 12
        }, this);
    }
    const isOwner = isAuthenticated && user?.id === item.user_id;
    const canInteract = isAuthenticated && !isOwner;
    const isSoldOut = item.remain === 0;
    // ✅ local state は廃止：表示は常に SWR の値（唯一の真実）
    const displayedFavorited = isFavorited;
    const displayedCount = favoritesCount;
    /* =========================
     ❤️ Favorite（確定版）
  ========================= */ const submitFavorite = async (e)=>{
        e.preventDefault();
        e.stopPropagation();
        if (!isAuthenticated || !auth.apiClient) {
            router.push("/login");
            return;
        }
        if (isTogglingFavorite) return;
        setIsTogglingFavorite(true);
        const next = !isFavorited;
        const detailKey = [
            "item-detail",
            item.id,
            "auth"
        ];
        // ① optimistic update
        (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])(detailKey, (current)=>{
            if (!current) return current;
            return {
                ...current,
                is_favorited: next,
                favorites_count: Math.max(0, (current.favorites_count ?? 0) + (next ? 1 : -1))
            };
        }, false);
        try {
            // ② API
            const res = next ? await auth.apiClient.post(`/reactions/items/${item.id}/favorite`) : await auth.apiClient.delete(`/reactions/items/${item.id}/favorite`);
            // ③ ★ここが決定打：サーバー真実で確定同期
            (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])(detailKey, {
                ...(await auth.apiClient.get(`/items/${item.id}`)).data
            }, false);
            // ④ マイリストも同期
            (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])("/items/favorite");
        } catch  {
            // rollback
            (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])(detailKey);
        } finally{
            setIsTogglingFavorite(false);
        }
    };
    /* =========================
     💬 Comment
  ========================= */ const submitComment = async ()=>{
        if (!item) return;
        if (!newComment.trim()) {
            setCommentErrors([
                "コメントを入力してください"
            ]);
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
                item_id: item.id,
                comment: newComment
            });
            setNewComment("");
            // ✅ ★ここが最重要修正点★
            const swrKey = [
                "item-detail",
                item.id,
                "auth"
            ];
            (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])(swrKey);
        } catch  {
            setCommentErrors([
                "コメント投稿に失敗しました"
            ]);
        } finally{
            setIsSubmittingComment(false);
        }
    };
    /* =========================
    表示用派生
  ========================= */ const brandTokens = Array.isArray(item.brands) ? item.brands : item.brand ? toTokenList(item.brand) : [];
    const categoryTokens = item?.tags?.category?.map((c)=>c.display_name) ?? (Array.isArray(item.category) ? item.category : []);
    const rawCondition = item.raw_condition ?? item.original_condition ?? item.condition ?? null;
    const rawColor = item.raw_color ?? item.original_color ?? item.color ?? null;
    const displayColor = item.color ?? null;
    const navigateToPurchase = ()=>{
        router.push(`/purchase/${item.id}`);
    };
    /* =========================
     JSX
  ========================= */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].item_detail_wrapper,
        children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].item_detail_contents,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].card,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].imageArea,
                        children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                            src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["getImageUrl"])(item.item_image),
                            onError: (e)=>(0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["onImageError"])(e, item.name),
                            alt: "商品写真",
                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].image
                        }, void 0, false, {
                            fileName: "[project]/src/app/item/[items_id]/page.tsx",
                            lineNumber: 224,
                            columnNumber: 13
                        }, this)
                    }, void 0, false, {
                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                        lineNumber: 223,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].infoArea,
                        children: [
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].itemTitle,
                                children: item.name
                            }, void 0, false, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 235,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].brandBlock,
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].brandLabel,
                                        children: "ブランド名"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 239,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        style: {
                                            display: "flex",
                                            flexWrap: "wrap",
                                            gap: 8,
                                            alignItems: "center"
                                        },
                                        children: brandTokens.length > 0 ? brandTokens.map((b, idx)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                type: "button",
                                                // ここは「将来UI向上ボタン」に育てられる（検索/同ブランド一覧/属性説明など）
                                                onClick: ()=>{
                                                    // v1: 動作確認用（必要なら後で実装を入れる）
                                                    // 例: router.push(`/search?brand=${encodeURIComponent(b)}`)
                                                    console.log("[brand token clicked]", b);
                                                },
                                                style: {
                                                    border: "1px solid rgba(0,0,0,0.15)",
                                                    borderRadius: 10,
                                                    padding: "6px 10px",
                                                    fontSize: 13,
                                                    lineHeight: 1,
                                                    background: "white",
                                                    cursor: "pointer",
                                                    maxWidth: 220
                                                },
                                                title: b,
                                                children: shortenLabel(b)
                                            }, `${b}-${idx}`, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 251,
                                                columnNumber: 21
                                            }, this)) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].brandValue,
                                            children: "未登録"
                                        }, void 0, false, {
                                            fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                            lineNumber: 276,
                                            columnNumber: 19
                                        }, this)
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 241,
                                        columnNumber: 15
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 238,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].priceBlock,
                                children: isSoldOut ? /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].priceSoldOut,
                                    children: "SOLD OUT"
                                }, void 0, false, {
                                    fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                    lineNumber: 284,
                                    columnNumber: 17
                                }, this) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].price,
                                    children: [
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].priceYen,
                                            children: "¥"
                                        }, void 0, false, {
                                            fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                            lineNumber: 287,
                                            columnNumber: 19
                                        }, this),
                                        item.price?.toLocaleString(),
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].priceAfter,
                                            children: " (税込)"
                                        }, void 0, false, {
                                            fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                            lineNumber: 289,
                                            columnNumber: 19
                                        }, this)
                                    ]
                                }, void 0, true, {
                                    fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                    lineNumber: 286,
                                    columnNumber: 17
                                }, this)
                            }, void 0, false, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 282,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].reactionRow,
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteBlock,
                                        children: [
                                            canInteract ? /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                type: "button",
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteBtn,
                                                onClick: (e)=>submitFavorite(e),
                                                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                    className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteIcon} ${displayedFavorited ? __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteActive : ""}`,
                                                    children: displayedFavorited ? "❤️" : "🤍"
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                    lineNumber: 303,
                                                    columnNumber: 21
                                                }, this)
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 298,
                                                columnNumber: 19
                                            }, this) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].disabledHeart,
                                                children: "🤍"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 312,
                                                columnNumber: 19
                                            }, this),
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteCount,
                                                children: displayedCount
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 315,
                                                columnNumber: 17
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 296,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentIconBlock,
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentIcon,
                                                children: "💬"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 319,
                                                columnNumber: 17
                                            }, this),
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentCount,
                                                children: comments.length
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 320,
                                                columnNumber: 17
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 318,
                                        columnNumber: 15
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 295,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: "item_detail_form pt-4",
                                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                    type: "button",
                                    onClick: ()=>{
                                        if (isOwner) {
                                            router.push("/mypage");
                                        } else if (!isAuthenticated) {
                                            router.push("/login");
                                        } else {
                                            navigateToPurchase();
                                        }
                                    },
                                    disabled: isSoldOut && !isOwner || isLoading,
                                    className: `w-full py-3 text-lg font-bold rounded-lg transition duration-200 shadow-lg ${!isSoldOut ? "bg-red-600 text-white hover:bg-red-700 active:bg-red-800" : "bg-gray-400 text-gray-700 cursor-not-allowed"}`,
                                    children: isOwner ? "マイページへ移動する" : !isAuthenticated ? "ログインして購入" : isSoldOut ? "SOLD OUT" : "カートへ"
                                }, void 0, false, {
                                    fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                    lineNumber: 326,
                                    columnNumber: 15
                                }, this)
                            }, void 0, false, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 325,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].section,
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].sectionTitle,
                                        children: "商品説明"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 356,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].explainText,
                                        children: item.explain
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 357,
                                        columnNumber: 15
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 355,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].section,
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].sectionTitle,
                                        children: "商品情報"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 362,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].categoryRow,
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].categoryLabel,
                                                children: "カテゴリー："
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 365,
                                                columnNumber: 17
                                            }, this),
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("ul", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].categoryList,
                                                children: categoryTokens.map((c)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("li", {
                                                        children: c
                                                    }, c, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 368,
                                                        columnNumber: 21
                                                    }, this))
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 366,
                                                columnNumber: 17
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 364,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionRow,
                                        style: {
                                            display: "flex",
                                            gap: 14
                                        },
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                style: {
                                                    flex: 1,
                                                    minWidth: 0
                                                },
                                                children: [
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionLabel,
                                                        children: "商品の状態："
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 379,
                                                        columnNumber: 19
                                                    }, this),
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionValue,
                                                        children: rawCondition || "未登録"
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 380,
                                                        columnNumber: 19
                                                    }, this)
                                                ]
                                            }, void 0, true, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 378,
                                                columnNumber: 17
                                            }, this),
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionRow,
                                                style: {
                                                    marginTop: 10
                                                },
                                                children: [
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionLabel,
                                                        children: "カラー："
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 395,
                                                        columnNumber: 19
                                                    }, this),
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].conditionValue,
                                                        children: displayColor || rawColor || "未登録"
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 396,
                                                        columnNumber: 19
                                                    }, this)
                                                ]
                                            }, void 0, true, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 394,
                                                columnNumber: 17
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 374,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].section,
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentHeader,
                                                children: [
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].sectionTitle,
                                                        children: "コメント"
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 405,
                                                        columnNumber: 19
                                                    }, this),
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentCountText,
                                                        children: [
                                                            "(",
                                                            comments.length,
                                                            ")"
                                                        ]
                                                    }, void 0, true, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 406,
                                                        columnNumber: 19
                                                    }, this)
                                                ]
                                            }, void 0, true, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 404,
                                                columnNumber: 17
                                            }, this),
                                            comments.length > 0 ? /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentList,
                                                children: comments.map((comment)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentItem,
                                                        children: [
                                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentUserRow,
                                                                children: [
                                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                                                        src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["getImageUrl"])(comment.user.user_image, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["IMAGE_TYPE"].USER),
                                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentUserImage,
                                                                        onError: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["onImageError"]
                                                                    }, void 0, false, {
                                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                                        lineNumber: 416,
                                                                        columnNumber: 27
                                                                    }, this),
                                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentUserName,
                                                                        children: comment.user.name
                                                                    }, void 0, false, {
                                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                                        lineNumber: 424,
                                                                        columnNumber: 27
                                                                    }, this)
                                                                ]
                                                            }, void 0, true, {
                                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                                lineNumber: 415,
                                                                columnNumber: 25
                                                            }, this),
                                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentText,
                                                                children: comment.comment
                                                            }, void 0, false, {
                                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                                lineNumber: 429,
                                                                columnNumber: 25
                                                            }, this),
                                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("small", {
                                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].commentDate,
                                                                children: [
                                                                    "投稿日時:",
                                                                    " ",
                                                                    new Date(comment.created_at).toLocaleString()
                                                                ]
                                                            }, void 0, true, {
                                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                                lineNumber: 431,
                                                                columnNumber: 25
                                                            }, this)
                                                        ]
                                                    }, comment.id, true, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 414,
                                                        columnNumber: 23
                                                    }, this))
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 412,
                                                columnNumber: 19
                                            }, this) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].noComments,
                                                children: "まだコメントはありません。"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 439,
                                                columnNumber: 19
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 403,
                                        columnNumber: 15
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].section,
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].sectionTitle,
                                                children: "商品へのコメント"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 447,
                                                columnNumber: 17
                                            }, this),
                                            commentErrors.length > 0 && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].errorBoxSmall,
                                                children: commentErrors.map((err, index)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                        children: err
                                                    }, index, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 452,
                                                        columnNumber: 23
                                                    }, this))
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 450,
                                                columnNumber: 19
                                            }, this),
                                            isAuthenticated ? /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["Fragment"], {
                                                children: [
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("textarea", {
                                                        value: newComment,
                                                        onChange: (e)=>setNewComment(e.target.value),
                                                        rows: 5,
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].textarea
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 459,
                                                        columnNumber: 21
                                                    }, this),
                                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                        type: "button",
                                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].submitBtn,
                                                        onClick: submitComment,
                                                        disabled: isSubmittingComment,
                                                        children: isSubmittingComment ? "投稿中..." : "コメントを送信する"
                                                    }, void 0, false, {
                                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                        lineNumber: 466,
                                                        columnNumber: 21
                                                    }, this)
                                                ]
                                            }, void 0, true) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$item$2f5b$items_id$5d2f$W$2d$ItemDetailView$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].submitBtn,
                                                onClick: ()=>router.push("/login"),
                                                style: {
                                                    cursor: "pointer"
                                                },
                                                children: "ログインしてコメントする"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                                lineNumber: 476,
                                                columnNumber: 19
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                        lineNumber: 446,
                                        columnNumber: 15
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                                lineNumber: 361,
                                columnNumber: 13
                            }, this)
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/item/[items_id]/page.tsx",
                        lineNumber: 233,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/item/[items_id]/page.tsx",
                lineNumber: 221,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/item/[items_id]/page.tsx",
            lineNumber: 220,
            columnNumber: 7
        }, this)
    }, void 0, false, {
        fileName: "[project]/src/app/item/[items_id]/page.tsx",
        lineNumber: 219,
        columnNumber: 5
    }, this);
}
_s(ItemDetailPage, "DCN1DA3TowweC+Uiytml79ZJVoA=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useParams"],
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRouter"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemDetailSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemDetailSWR"]
    ];
});
_c = ItemDetailPage;
var _c;
__turbopack_context__.k.register(_c, "ItemDetailPage");
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-client] (ecmascript) <export j as mutate>", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "mutate",
    ()=>__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__["j"]
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-client] (ecmascript)");
}),
]);

//# sourceMappingURL=_eff8d1c7._.js.map