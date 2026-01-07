module.exports = [
"[project]/src/services/useItemListSWR.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemListSWR",
    ()=>useItemListSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-ssr] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
;
;
;
const useItemListSWR = ()=>{
    const { apiClient, user, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
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
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"].get("/api/items/public");
        return res.data;
    };
    const swrKey = isLoading ? null : user ? [
        "public-items",
        user.id
    ] : [
        "public-items",
        "guest"
    ];
    const { data, error, isLoading: swrLoading, mutate } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(swrKey, fetcher);
    return {
        items: data?.items ?? [],
        isLoading: isLoading || swrLoading,
        error,
        mutateItems: mutate
    };
};
}),
"[project]/src/services/useItemSearchSWR.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemSearchSWR",
    ()=>useItemSearchSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-ssr] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
;
;
;
const useItemSearchSWR = (query)=>{
    const { apiClient, isAuthenticated, isLoading: authLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const shouldFetch = !authLoading && query.trim().length > 0;
    /**
   * ★ 超重要：auth 状態で key を分離
   */ const key = shouldFetch ? [
        "search-items",
        query,
        isAuthenticated ? "auth" : "guest"
    ] : null;
    const fetcher = async ()=>{
        // 🔑 認証あり
        if (apiClient) {
            const res = await apiClient.get(`/search/items?q=${encodeURIComponent(query)}`);
            return res.data;
        }
        // 👤 ゲスト（同じエンドポイント）
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"].get(`/api/search/items?q=${encodeURIComponent(query)}`);
        return res.data;
    };
    const { data, error, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher);
    return {
        items: data?.items ?? [],
        isLoading: authLoading || isLoading,
        error
    };
};
}),
"[project]/src/services/useFavoriteItemsSWR.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "FAVORITE_ITEMS_SWR_KEY",
    ()=>FAVORITE_ITEMS_SWR_KEY,
    "useFavoriteItemsSWR",
    ()=>useFavoriteItemsSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-ssr] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
;
;
const FAVORITE_ITEMS_SWR_KEY = "/items/favorite";
const useFavoriteItemsSWR = ()=>{
    const { apiClient, isAuthenticated, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const swrKey = !isLoading && isAuthenticated && apiClient ? "/items/favorite" : null;
    const fetcher = async ()=>{
        const res = await apiClient.get("/items/favorite");
        return res.data;
    };
    const { data, error, isLoading: swrLoading, mutate } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(swrKey, fetcher, {
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
}),
"[project]/src/utils/utils.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
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
var IMAGE_TYPE = /*#__PURE__*/ function(IMAGE_TYPE) {
    IMAGE_TYPE["USER"] = "user";
    IMAGE_TYPE["ITEM"] = "item";
    IMAGE_TYPE["OTHER"] = "other";
    return IMAGE_TYPE;
}({});
// ======================================
// Backend Base URL
// ======================================
const BACKEND_BASE_URL = process.env.NEXT_PUBLIC_BACKEND_URL ?? "https://localhost";
// ======================================
// Fallback Images（実在パス）
// ======================================
const DEFAULT_USER_IMAGE = `${BACKEND_BASE_URL}/pictures_user/default-profile2.jpg`;
const DEFAULT_ITEM_IMAGE = `${BACKEND_BASE_URL}/storage/pictures/no-image.png`;
function getImageUrl(path, type = "other") {
    if (!path) {
        return type === "user" ? DEFAULT_USER_IMAGE : DEFAULT_ITEM_IMAGE;
    }
    if (path.startsWith("http://") || path.startsWith("https://")) {
        return path;
    }
    if (path.startsWith("/storage/")) {
        return `${BACKEND_BASE_URL}${path}`;
    }
    if (path.startsWith("pictures_user/") || path.startsWith("item_images/") || path.startsWith("pictures/")) {
        return `${BACKEND_BASE_URL}/storage/${path}`;
    }
    return `${BACKEND_BASE_URL}/${path}`;
}
const onImageError = (e)=>{
    const img = e.currentTarget;
    img.onerror = null;
    img.src = "https://placehold.co/300x300?text=No+Image";
};
}),
"[project]/src/app/W-Resource-Rich-Simulation-Center-Home.module.css [app-ssr] (css module)", ((__turbopack_context__) => {

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
"[project]/src/app/page.tsx [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>Home
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react-jsx-dev-runtime.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/client/app-dir/link.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-ssr] (ecmascript) <export j as mutate>");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemListSWR.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemSearchSWR.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useFavoriteItemsSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useFavoriteItemsSWR.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/W-Resource-Rich-Simulation-Center-Home.module.css [app-ssr] (css module)");
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
;
function Home() {
    const router = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useRouter"])();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const { isAuthenticated, isLoading: isAuthLoading, apiClient } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    /* =========================
     🔐 Profile Gate state
  ========================= */ const [profileChecked, setProfileChecked] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(false);
    const [hasProfile, setHasProfile] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(null);
    /* =========================
     🔐 Profile 判定
  ========================= */ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useEffect"])(()=>{
        if (!isAuthenticated || !apiClient) return;
        let cancelled = false;
        (async ()=>{
            try {
                const res = await apiClient.get("/mypage/profile");
                if (cancelled) return;
                setHasProfile(!!res.data?.has_profile);
                setProfileChecked(true);
            } catch  {
                setHasProfile(false);
                setProfileChecked(true);
            }
        })();
        return ()=>{
            cancelled = true;
        };
    }, [
        isAuthenticated,
        apiClient
    ]);
    /* =========================
     🚦 Profile 未作成なら遷移
  ========================= */ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useEffect"])(()=>{
        if (isAuthenticated && profileChecked && hasProfile === false) {
            router.replace("/mypage/profile");
        }
    }, [
        isAuthenticated,
        profileChecked,
        hasProfile,
        router
    ]);
    /* =========================
     🔖 タブ・検索状態
  ========================= */ const currentTab = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>searchParams.get("tab") === "mylist" ? "mylist" : "all", [
        searchParams
    ]);
    const currentSearchQuery = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>searchParams.get("all_item_search") || "", [
        searchParams
    ]);
    const isSearch = currentSearchQuery.trim().length > 0;
    /* =========================
     📦 データ Hooks（必ず呼ぶ）
  ========================= */ const listResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useItemListSWR"])();
    const searchResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useItemSearchSWR"])(currentSearchQuery);
    const favoriteResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useFavoriteItemsSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useFavoriteItemsSWR"])();
    const isItemsLoading = currentTab === "mylist" ? favoriteResult.isLoading : isSearch ? searchResult.isLoading : listResult.isLoading;
    /* =========================
     🧠 表示アイテム（★ return より前）
  ========================= */ const items = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>{
        const raw = currentTab === "mylist" ? favoriteResult.items : isSearch ? searchResult.items : listResult.items;
        return raw.map((item)=>{
            const isSearchItem = isSearch;
            return {
                id: item.id,
                name: item.name,
                // ★ ここが核心
                price: isSearchItem ? item.price.amount : item.price,
                // Search は画像を返さない
                itemImagePath: isSearchItem ? null : item.itemImagePath ?? item.item_image ?? null,
                displayType: item.displayType ?? null,
                isFavorited: item.isFavorited ?? false
            };
        });
    }, [
        currentTab,
        isSearch,
        favoriteResult.items,
        searchResult.items,
        listResult.items
    ]);
    /* =========================
     ⛔ Gate / Loading 判定
  ========================= */ const isGateLoading = isAuthenticated && (!profileChecked || hasProfile === null);
    const isPageLoading = isAuthLoading || isItemsLoading || isGateLoading;
    /* =========================
     ❤️ いいね切替
  ========================= */ const toggleFavorite = async (item, isFavorited)=>{
        if (!apiClient) return;
        try {
            if (isFavorited) {
                await apiClient.delete(`/reactions/items/${item.id}/favorite`);
            } else {
                await apiClient.post(`/reactions/items/${item.id}/favorite`);
            }
            (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$export__j__as__mutate$3e$__["mutate"])("/items/favorite");
            await favoriteResult.refetchFavorites();
        } catch (e) {
            console.error(e);
        }
    };
    /* =========================
     🎨 return はここだけ
  ========================= */ if (isGateLoading) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].main_contents,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].loadingBox,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].spinner
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 159,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].loadingText,
                        children: "確認中..."
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 160,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/page.tsx",
                lineNumber: 158,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/page.tsx",
            lineNumber: 157,
            columnNumber: 7
        }, this);
    }
    return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].main_contents,
        children: [
            isPageLoading && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].loadingBox,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].spinner
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 170,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].loadingText,
                        children: "読み込み中..."
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 171,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/page.tsx",
                lineNumber: 169,
                columnNumber: 9
            }, this),
            !isPageLoading && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["Fragment"], {
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].shopButtons,
                        children: [
                            "a",
                            "b",
                            "c",
                            "d"
                        ].map((code)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                onClick: ()=>router.push(`/shops/shop-${code}`),
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].shopButton,
                                children: [
                                    "テストリンク ショップ",
                                    code.toUpperCase()
                                ]
                            }, code, true, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 180,
                                columnNumber: 15
                            }, this))
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 178,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].main_select,
                        children: [
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"], {
                                href: {
                                    pathname: "/",
                                    query: {
                                        tab: "all",
                                        all_item_search: currentSearchQuery
                                    }
                                },
                                className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].recs} ${currentTab === "all" ? __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].active : ""}`,
                                children: "すべて"
                            }, void 0, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 192,
                                columnNumber: 13
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"], {
                                href: {
                                    pathname: "/",
                                    query: {
                                        tab: "mylist"
                                    }
                                },
                                className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].mylists} ${currentTab === "mylist" ? __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].active : ""}`,
                                children: "マイリスト"
                            }, void 0, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 204,
                                columnNumber: 13
                            }, this)
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 191,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].items_select,
                        children: items.length > 0 ? items.map((item)=>{
                            const isFavorited = item.isFavorited === true;
                            return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].items_select_all,
                                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].cardLink,
                                    role: "button",
                                    tabIndex: 0,
                                    onClick: ()=>router.push(`/item/${item.id}`),
                                    children: [
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].itemImageWrapper,
                                            children: [
                                                item.displayType && item.displayType !== "FAVORITE" && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].ownStar,
                                                    children: item.displayType === "STAR" ? "⭐️" : "💫"
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 231,
                                                    columnNumber: 29
                                                }, this),
                                                isAuthenticated && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].favoriteButton,
                                                    onClick: (e)=>{
                                                        e.stopPropagation();
                                                        toggleFavorite(item, isFavorited);
                                                    },
                                                    children: isFavorited ? "❤️" : "🤍"
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 237,
                                                    columnNumber: 27
                                                }, this),
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                                    src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["getImageUrl"])(item.itemImagePath, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["IMAGE_TYPE"].ITEM),
                                                    alt: item.name,
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].itemImage,
                                                    onError: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["onImageError"]
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 248,
                                                    columnNumber: 25
                                                }, this)
                                            ]
                                        }, void 0, true, {
                                            fileName: "[project]/src/app/page.tsx",
                                            lineNumber: 228,
                                            columnNumber: 23
                                        }, this),
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].item_info,
                                            children: [
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].item_name,
                                                    children: item.name
                                                }, void 0, false, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 257,
                                                    columnNumber: 25
                                                }, this),
                                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].item_price,
                                                    children: [
                                                        "¥",
                                                        typeof item.price === "number" ? item.price.toLocaleString() : "-"
                                                    ]
                                                }, void 0, true, {
                                                    fileName: "[project]/src/app/page.tsx",
                                                    lineNumber: 258,
                                                    columnNumber: 25
                                                }, this)
                                            ]
                                        }, void 0, true, {
                                            fileName: "[project]/src/app/page.tsx",
                                            lineNumber: 256,
                                            columnNumber: 23
                                        }, this)
                                    ]
                                }, void 0, true, {
                                    fileName: "[project]/src/app/page.tsx",
                                    lineNumber: 222,
                                    columnNumber: 21
                                }, this)
                            }, item.id, false, {
                                fileName: "[project]/src/app/page.tsx",
                                lineNumber: 221,
                                columnNumber: 19
                            }, this);
                        }) : /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$W$2d$Resource$2d$Rich$2d$Simulation$2d$Center$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].no_items,
                            children: currentTab === "mylist" && !isAuthenticated ? "マイリストを見るにはログインが必要です。" : "該当する商品が見つかりませんでした。"
                        }, void 0, false, {
                            fileName: "[project]/src/app/page.tsx",
                            lineNumber: 269,
                            columnNumber: 15
                        }, this)
                    }, void 0, false, {
                        fileName: "[project]/src/app/page.tsx",
                        lineNumber: 215,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true)
        ]
    }, void 0, true, {
        fileName: "[project]/src/app/page.tsx",
        lineNumber: 167,
        columnNumber: 5
    }, this);
}
}),
"[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-ssr] (ecmascript) <export j as mutate>", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "mutate",
    ()=>__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["j"]
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$_internal$2f$config$2d$context$2d$12s$2d$Dh3trQsc$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/_internal/config-context-12s-Dh3trQsc.mjs [app-ssr] (ecmascript)");
}),
];

//# sourceMappingURL=_941838e9._.js.map