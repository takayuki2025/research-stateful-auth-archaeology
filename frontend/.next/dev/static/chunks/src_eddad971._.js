(globalThis.TURBOPACK || (globalThis.TURBOPACK = [])).push([typeof document === "object" ? document.currentScript : undefined,
"[project]/src/services/useItemListSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemListSWR",
    ()=>useItemListSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
;
const useItemListSWR = ()=>{
    _s();
    const { apiClient, user, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    const fetcher = async ()=>{
        // 🔐 認証済み
        if (apiClient && user) {
            const res = await apiClient.get("/items/public", {
                params: {
                    viewer_user_id: user.id
                }
            });
            return res.data;
        }
        // 👤 ゲスト
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"].get("/api/items/public");
        return res.data;
    };
    const swrKey = isLoading ? null : user ? [
        "public-items",
        user.id
    ] : [
        "public-items",
        "guest"
    ];
    const { data, error, isLoading: swrLoading, mutate } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(swrKey, fetcher);
    return {
        items: data?.items ?? [],
        isLoading: isLoading || swrLoading,
        error,
        mutateItems: mutate
    };
};
_s(useItemListSWR, "8cyOZ39hJ8um+mB2v4dliK0S1z8=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"],
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"]
    ];
});
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/services/useItemSearchSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemSearchSWR",
    ()=>useItemSearchSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
const useItemSearchSWR = (query)=>{
    _s();
    const key = query ? [
        "search",
        "items",
        query
    ] : null;
    const fetcher = async ()=>{
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"].get(`/api/search/items?q=${encodeURIComponent(query)}`);
        return res.data;
    };
    const { data, error, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher);
    return {
        items: data?.items ?? [],
        isLoading,
        error
    };
};
_s(useItemSearchSWR, "3etLDUffADz62tD7g9gJKxYxEy4=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"]
    ];
});
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/services/useFavoriteItemsSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "FAVORITE_ITEMS_SWR_KEY",
    ()=>FAVORITE_ITEMS_SWR_KEY,
    "useFavoriteItemsSWR",
    ()=>useFavoriteItemsSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";
const useFavoriteItemsSWR = ()=>{
    _s();
    const { apiClient, isAuthenticated, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    const swrKey = !isLoading && isAuthenticated && apiClient ? "/items/favorite" : null;
    const fetcher = async ()=>{
        const res = await apiClient.get("/items/favorite");
        return res.data;
    };
    const { data, error, isLoading: swrLoading, mutate } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(swrKey, fetcher, {
        revalidateOnFocus: false,
        revalidateOnReconnect: false,
        revalidateIfStale: false
    });
    return {
        items: data?.items ?? [],
        isLoading: isLoading || swrLoading,
        error,
        /** ★ これだけ使う */ refetchFavorites: ()=>mutate()
    };
};
_s(useFavoriteItemsSWR, "75AowcAqzltXe7Apw4JcysKx5kk=", false, function() {
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
"[project]/src/app/W-Resource-Rich-Simulation-Center-Home.module.css [app-client] (css module)", ((__turbopack_context__) => {

__turbopack_context__.v({
  "active": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__active",
  "cardLink": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__cardLink",
  "itemImage": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__itemImage",
  "itemImageWrapper": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__itemImageWrapper",
  "itemLink": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__itemLink",
  "item_info": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__item_info",
  "item_name": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__item_name",
  "item_price": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__item_price",
  "items_select": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__items_select",
  "items_select_all": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__items_select_all",
  "loadingBox": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__loadingBox",
  "loadingText": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__loadingText",
  "main_contents": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__main_contents",
  "main_select": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__main_select",
  "mylists": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__mylists",
  "no_items": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__no_items",
  "ownStar": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__ownStar",
  "recs": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__recs",
  "shopButton": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__shopButton",
  "shopButtons": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__shopButtons",
  "sold_text": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__sold_text",
  "spin": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__spin",
  "spinner": "W-Resource-Rich-Simulation-Center-Home-module__yOhjGG__spinner",
});
}),
"[project]/src/app/page.tsx [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>Home
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/jsx-dev-runtime.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/index.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/client/app-dir/link.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemListSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemSearchSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useFavoriteItemsSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useFavoriteItemsSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/W-Resource-Rich-Simulation-Center-Home.module.css [app-client] (css module)");
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
;
;
;
function Home() {
    _s();
    const router = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRouter"])();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const { isAuthenticated, isLoading: isAuthLoading, apiClient } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    /* =========================
     🔖 タブ状態
  ========================= */ const currentTab = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "Home.useMemo[currentTab]": ()=>searchParams.get("tab") === "mylist" ? "mylist" : "all"
    }["Home.useMemo[currentTab]"], [
        searchParams
    ]);
    /* =========================
   ★ FIX：タブ切替時に強制再取得
========================= */ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useEffect"])({
        "Home.useEffect": ()=>{
            if (currentTab === "mylist") {
                favoriteResult.refetchFavorites();
            }
        }
    }["Home.useEffect"], [
        currentTab
    ]);
    /* =========================
     🔍 検索状態
  ========================= */ const currentSearchQuery = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "Home.useMemo[currentSearchQuery]": ()=>searchParams.get("all_item_search") || ""
    }["Home.useMemo[currentSearchQuery]"], [
        searchParams
    ]);
    const isSearch = currentSearchQuery.trim().length > 0;
    /* =========================
     📦 Hooks
  ========================= */ const listResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemListSWR"])();
    const searchResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemSearchSWR"])(currentSearchQuery);
    const favoriteResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useFavoriteItemsSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useFavoriteItemsSWR"])();
    /* =========================
     ❤️ 楽観更新：いいね切替
  ========================= */ const toggleFavorite = async (item, isFavorited)=>{
        if (!apiClient) return;
        try {
            if (isFavorited) {
                await apiClient.delete(`/reactions/items/${item.id}/favorite`);
            } else {
                await apiClient.post(`/reactions/items/${item.id}/favorite`);
            }
            /** ★ 成功したら必ず再取得 */ await favoriteResult.refetchFavorites();
        } catch (e) {
            console.error(e);
        }
    };
    /* =========================
     🧠 PublicItem 正規化
  ========================= */ const items = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "Home.useMemo[items]": ()=>{
            const rawItems = currentTab === "mylist" ? favoriteResult.items : isSearch ? searchResult.items : listResult.items;
            return rawItems.map({
                "Home.useMemo[items]": (item)=>({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        itemImagePath: item.itemImagePath ?? item.item_image ?? null,
                        brandPrimary: item.brandPrimary ?? null,
                        conditionName: item.conditionName ?? null,
                        colorName: item.colorName ?? null,
                        publishedAt: item.publishedAt ?? null,
                        displayType: item.displayType ?? null,
                        // ❤️ ここ
                        isFavorited: item.isFavorited ?? false
                    })
            }["Home.useMemo[items]"]);
        }
    }["Home.useMemo[items]"], [
        currentTab,
        isSearch,
        favoriteResult.items,
        searchResult.items,
        listResult.items
    ]);
    const isItemsLoading = currentTab === "mylist" ? favoriteResult.isLoading : isSearch ? searchResult.isLoading : listResult.isLoading;
    const isPageLoading = isAuthLoading || isItemsLoading;
    /* =========================
     🎨 UI
  ========================= */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].main_contents,
        children: [
            isPageLoading && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].loadingBox,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].spinner
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 129,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].loadingText,
                        children: "読み込み中..."
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 130,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/page.tsx",
                lineNumber: 128,
                columnNumber: 9
            }, this),
            !isPageLoading && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["Fragment"], {
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].main_select,
                        children: [
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                                href: {
                                    pathname: "/",
                                    query: {
                                        tab: "all",
                                        all_item_search: currentSearchQuery
                                    }
                                },
                                className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].recs} ${currentTab === "all" ? __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].active : ""}`,
                                children: "すべて"
                            }, void 0, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 138,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                                href: {
                                    pathname: "/",
                                    query: {
                                        tab: "mylist"
                                    }
                                },
                                className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].mylists} ${currentTab === "mylist" ? __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].active : ""}`,
                                children: "マイリスト"
                            }, void 0, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 150,
                                columnNumber: 13
                            }, this)
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 137,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].items_select,
                        children: items.length > 0 ? items.map((item)=>{
                            const isFavorited = item.isFavorited === true;
                            return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].items_select_all,
                                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].cardLink,
                                    role: "button",
                                    tabIndex: 0,
                                    onClick: ()=>router.push(`/item/${item.id}`),
                                    children: [
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].itemImageWrapper,
                                            children: [
                                                item.displayType && item.displayType !== "FAVORITE" && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].ownStar,
                                                    children: item.displayType === "STAR" ? "⭐️" : "💫"
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 178,
                                                    columnNumber: 29
                                                }, this),
                                                isAuthenticated && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].favoriteButton,
                                                    onClick: (e)=>{
                                                        e.stopPropagation();
                                                        toggleFavorite(item, isFavorited);
                                                    },
                                                    children: isFavorited ? "❤️" : "🤍"
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 185,
                                                    columnNumber: 27
                                                }, this),
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                                    src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["getImageUrl"])(item.itemImagePath, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["IMAGE_TYPE"].ITEM),
                                                    alt: item.name,
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].itemImage,
                                                    onError: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["onImageError"]
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 196,
                                                    columnNumber: 25
                                                }, this)
                                            ]
                                        }, void 0, true, {
                                            fileName: "[project]/src/app/page.tsx",
                                            lineNumber: 174,
                                            columnNumber: 23
                                        }, this),
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].item_info,
                                            children: [
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].item_name,
                                                    children: item.name
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 205,
                                                    columnNumber: 25
                                                }, this),
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].item_price,
                                                    children: [
                                                        "¥",
                                                        item.price.toLocaleString()
                                                    ]
                                                }, void 0, true, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 206,
                                                    columnNumber: 25
                                                }, this)
                                            ]
                                        }, void 0, true, {
                                            fileName: "[project]/src/app/page.tsx",
                                            lineNumber: 204,
                                            columnNumber: 23
                                        }, this)
                                    ]
                                }, void 0, true, {
                                    fileName: "[project]/src/app/page.tsx",
                                    lineNumber: 168,
                                    columnNumber: 21
                                }, this)
                            }, item.id, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 167,
                                columnNumber: 19
                            }, this);
                        }) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].no_items,
                            children: currentTab === "mylist" && !isAuthenticated ? "マイリストを見るにはログインが必要です。" : "該当する商品が見つかりませんでした。"
                        }, void 0, false, {
                            fileName: "[project]/src/app/page.tsx",
                            lineNumber: 215,
                            columnNumber: 15
                        }, this)
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 161,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true)
        ]
    }, void 0, true, {
        fileName: "[project]/src/app/page.tsx",
        lineNumber: 126,
        columnNumber: 5
    }, this);
}
_s(Home, "UGqcyV8/koyKNyP+EpAZICx+80M=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRouter"],
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemListSWR"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemSearchSWR"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useFavoriteItemsSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useFavoriteItemsSWR"]
    ];
});
_c = Home;
var _c;
__turbopack_context__.k.register(_c, "Home");
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
]);

//# sourceMappingURL=src_eddad971._.js.map