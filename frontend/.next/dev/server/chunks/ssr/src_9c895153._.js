module.exports = [
"[project]/src/services/useItemListByShopSWR.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemListByShopSWR",
    ()=>useItemListByShopSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-ssr] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
;
;
function useItemListByShopSWR(shopCode) {
    const { apiClient, isReady } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const canFetch = isReady && !!apiClient && typeof shopCode === "string" && shopCode.length > 0;
    const key = canFetch ? [
        "shop-items",
        shopCode
    ] : null;
    const fetcher = async ()=>{
        // ★ ここに来る時点で apiClient は必ず存在
        const res = await apiClient.get(`/shops/${shopCode}/items`);
        return res.data;
    };
    const { data, error, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher, {
        revalidateOnFocus: false,
        revalidateOnReconnect: false
    });
    return {
        items: data?.items ?? [],
        isLoading: !canFetch || isLoading,
        error
    };
}
}),
"[project]/src/services/useItemSearchByShopSWR.ts [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemSearchByShopSWR",
    ()=>useItemSearchByShopSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-ssr] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
;
;
const useItemSearchByShopSWR = (shopCode, query)=>{
    const { apiClient, isReady } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const shouldFetch = typeof shopCode === "string" && shopCode.length > 0;
    console.log("[SWR] shouldFetch", {
        isReady,
        hasApiClient: !!apiClient,
        shopCode
    });
    const key = shouldFetch ? [
        "shop-items",
        shopCode
    ] : null;
    const fetcher = async ()=>{
        console.log("[SWR] fetching shop items", shopCode);
        const res = await apiClient.get(`/shops/${shopCode}/items`);
        console.log("[SWR] response", res.data);
        return res.data;
    };
    const { data, isLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher);
    return {
        items: data?.items ?? [],
        isLoading
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
"[project]/src/app/shops/[shop_code]/W-Shop-Home.module.css [app-ssr] (css module)", ((__turbopack_context__) => {

__turbopack_context__.v({
  "cardLink": "W-Shop-Home-module__MFieeG__cardLink",
  "dashboardButton": "W-Shop-Home-module__MFieeG__dashboardButton",
  "items_select": "W-Shop-Home-module__MFieeG__items_select",
  "items_select_all": "W-Shop-Home-module__MFieeG__items_select_all",
  "loadingBox": "W-Shop-Home-module__MFieeG__loadingBox",
  "main_contents": "W-Shop-Home-module__MFieeG__main_contents",
  "notice": "W-Shop-Home-module__MFieeG__notice",
  "shopHeader": "W-Shop-Home-module__MFieeG__shopHeader",
  "title": "W-Shop-Home-module__MFieeG__title",
});
}),
"[project]/src/app/shops/[shop_code]/page.tsx [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ShopHomePage
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react-jsx-dev-runtime.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/client/app-dir/link.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListByShopSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemListByShopSWR.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchByShopSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemSearchByShopSWR.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/shops/[shop_code]/W-Shop-Home.module.css [app-ssr] (css module)");
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
function ShopHomePage() {
    const { shop_code } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useParams"])();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const { user, isAuthenticated, isLoading: authLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    /* =========================
     🔍 検索状態
  ========================= */ const currentSearchQuery = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>searchParams.get("q") || "", [
        searchParams
    ]);
    const isSearch = currentSearchQuery.trim().length > 0;
    /* =========================
     📦 商品取得
  ========================= */ const listResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListByShopSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useItemListByShopSWR"])(shop_code);
    const searchResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchByShopSWR$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useItemSearchByShopSWR"])(shop_code, currentSearchQuery);
    const items = isSearch ? searchResult.items : listResult.items;
    const isPageLoading = authLoading || (isSearch ? searchResult.isLoading : listResult.isLoading);
    /* =========================
     🔐 このショップのスタッフか？
  ========================= */ // type Old = { shop_id: number };
    // type New = { shop_id: number; shop_code: string; role: string };
    const isShopStaff = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>{
        if (!isAuthenticated || !user?.shop_roles) return false;
        return user.shop_roles.some((r)=>r.shop_code === shop_code && [
                "owner",
                "manager",
                "staff"
            ].includes(r.role));
    }, [
        isAuthenticated,
        user,
        shop_code
    ]);
    /* =========================
     ⏳ Loading
  ========================= */ if (isPageLoading) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].loadingBox,
            children: "読み込み中..."
        }, void 0, false, {
            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
            lineNumber: 59,
            columnNumber: 12
        }, this);
    }
    /* =========================
     🎨 UI
  ========================= */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].main_contents,
        children: [
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].shopHeader,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("h1", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].title,
                        children: [
                            "Shop: ",
                            shop_code
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                        lineNumber: 69,
                        columnNumber: 9
                    }, this),
                    isShopStaff && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"], {
                        href: `/shops/${shop_code}/dashboard`,
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].dashboardButton,
                        children: "管理画面"
                    }, void 0, false, {
                        fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                        lineNumber: 72,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                lineNumber: 68,
                columnNumber: 7
            }, this),
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].items_select,
                children: items.map((item)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].items_select_all,
                        children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["default"], {
                            href: `/item/${item.id}`,
                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].cardLink,
                            children: [
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                    src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["getImageUrl"])(item.item_image),
                                    alt: item.name
                                }, void 0, false, {
                                    fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                    lineNumber: 86,
                                    columnNumber: 15
                                }, this),
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                    children: [
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                            children: item.name
                                        }, void 0, false, {
                                            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                            lineNumber: 88,
                                            columnNumber: 17
                                        }, this),
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                            children: [
                                                "¥",
                                                item.price?.toLocaleString()
                                            ]
                                        }, void 0, true, {
                                            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                            lineNumber: 89,
                                            columnNumber: 17
                                        }, this)
                                    ]
                                }, void 0, true, {
                                    fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                    lineNumber: 87,
                                    columnNumber: 15
                                }, this)
                            ]
                        }, void 0, true, {
                            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                            lineNumber: 85,
                            columnNumber: 13
                        }, this)
                    }, item.id, false, {
                        fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                        lineNumber: 84,
                        columnNumber: 11
                    }, this))
            }, void 0, false, {
                fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                lineNumber: 82,
                columnNumber: 7
            }, this),
            !isAuthenticated && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].notice,
                children: "ログインすると購入やマイリストが使えます。"
            }, void 0, false, {
                fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                lineNumber: 97,
                columnNumber: 9
            }, this)
        ]
    }, void 0, true, {
        fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
        lineNumber: 66,
        columnNumber: 5
    }, this);
}
}),
];

//# sourceMappingURL=src_9c895153._.js.map