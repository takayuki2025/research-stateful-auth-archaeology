(globalThis.TURBOPACK || (globalThis.TURBOPACK = [])).push([typeof document === "object" ? document.currentScript : undefined,
"[project]/src/infrastructure/http/publicClient.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "publicClient",
    ()=>publicClient
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$build$2f$polyfills$2f$process$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = /*#__PURE__*/ __turbopack_context__.i("[project]/node_modules/next/dist/build/polyfills/process.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/axios/lib/axios.js [app-client] (ecmascript)");
;
const publicClient = __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$axios$2f$lib$2f$axios$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"].create({
    baseURL: ("TURBOPACK compile-time value", "") || "/api",
    withCredentials: true
});
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/services/useItemListByShopSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemListByShopSWR",
    ()=>useItemListByShopSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$infrastructure$2f$http$2f$publicClient$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/infrastructure/http/publicClient.ts [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
function useItemListByShopSWR(shopCode) {
    _s();
    const canFetch = typeof shopCode === "string" && shopCode.length > 0;
    const key = canFetch ? [
        "shop-items",
        shopCode
    ] : null;
    const fetcher = async ()=>{
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$infrastructure$2f$http$2f$publicClient$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["publicClient"].get(`/shops/${shopCode}/items`);
        return res.data;
    };
    const { data, isLoading, error } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher, {
        revalidateOnFocus: false,
        revalidateOnReconnect: false
    });
    return {
        items: data?.items ?? [],
        isLoading,
        error
    };
}
_s(useItemListByShopSWR, "t/CNG0QHqQ97IXTJS79UsUByRzk=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"]
    ];
});
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/services/useItemSearchByShopSWR.ts [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "useItemSearchByShopSWR",
    ()=>useItemSearchByShopSWR
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__ = __turbopack_context__.i("[project]/node_modules/swr/dist/index/index.mjs [app-client] (ecmascript) <locals>");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$infrastructure$2f$http$2f$publicClient$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/infrastructure/http/publicClient.ts [app-client] (ecmascript)");
var _s = __turbopack_context__.k.signature();
;
;
function useItemSearchByShopSWR(shopCode, query) {
    _s();
    const canFetch = typeof shopCode === "string" && shopCode.length > 0 && typeof query === "string" && query.trim().length > 0;
    const key = canFetch ? [
        "shop-item-search",
        shopCode,
        query
    ] : null;
    const fetcher = async ()=>{
        const res = await __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$infrastructure$2f$http$2f$publicClient$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["publicClient"].get(`/search/shop-items`, {
            params: {
                shop_code: shopCode,
                keyword: query
            }
        });
        return res.data;
    };
    const { data, isLoading, error } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$swr$2f$dist$2f$index$2f$index$2e$mjs__$5b$app$2d$client$5d$__$28$ecmascript$29$__$3c$locals$3e$__["default"])(key, fetcher, {
        revalidateOnFocus: false
    });
    return {
        items: data?.items ?? [],
        isLoading,
        error
    };
}
_s(useItemSearchByShopSWR, "t/CNG0QHqQ97IXTJS79UsUByRzk=", false, function() {
    return [
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
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
"[project]/src/app/shops/[shop_code]/W-Shop-Home.module.css [app-client] (css module)", ((__turbopack_context__) => {

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
"[project]/src/app/shops/[shop_code]/page.tsx [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ShopHomePage
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/jsx-dev-runtime.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/index.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/client/app-dir/link.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemListByShopSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/services/useItemSearchByShopSWR.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/shops/[shop_code]/W-Shop-Home.module.css [app-client] (css module)");
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
function ShopHomePage() {
    _s();
    const { shop_code } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useParams"])();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const { user, isAuthenticated, isLoading: authLoading } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    /* =========================
     🔍 検索状態
  ========================= */ const currentSearchQuery = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "ShopHomePage.useMemo[currentSearchQuery]": ()=>searchParams.get("q") || ""
    }["ShopHomePage.useMemo[currentSearchQuery]"], [
        searchParams
    ]);
    const isSearch = currentSearchQuery.trim().length > 0;
    /* =========================
     📦 商品取得
  ========================= */ const listResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemListByShopSWR"])(shop_code);
    const searchResult = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemSearchByShopSWR"])(shop_code, currentSearchQuery);
    const items = isSearch ? searchResult.items : listResult.items;
    const isPageLoading = authLoading || (isSearch ? searchResult.isLoading : listResult.isLoading);
    /* =========================
     🔐 このショップのスタッフか？
  ========================= */ // type Old = { shop_id: number };
    // type New = { shop_id: number; shop_code: string; role: string };
    const isShopStaff = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useMemo"])({
        "ShopHomePage.useMemo[isShopStaff]": ()=>{
            if (!isAuthenticated || !user?.shop_roles) return false;
            return user.shop_roles.some({
                "ShopHomePage.useMemo[isShopStaff]": (r)=>r.shop_code === shop_code && [
                        "owner",
                        "manager",
                        "staff"
                    ].includes(r.role)
            }["ShopHomePage.useMemo[isShopStaff]"]);
        }
    }["ShopHomePage.useMemo[isShopStaff]"], [
        isAuthenticated,
        user,
        shop_code
    ]);
    /* =========================
     ⏳ Loading
  ========================= */ if (isPageLoading) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].loadingBox,
            children: "読み込み中..."
        }, void 0, false, {
            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
            lineNumber: 59,
            columnNumber: 12
        }, this);
    }
    /* =========================
     🎨 UI
  ========================= */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].main_contents,
        children: [
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].shopHeader,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h1", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].title,
                        children: [
                            "Shop: ",
                            shop_code
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                        lineNumber: 69,
                        columnNumber: 9
                    }, this),
                    isShopStaff && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                        href: `/shops/${shop_code}/dashboard`,
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].dashboardButton,
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
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].items_select,
                children: items.map((item)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].items_select_all,
                        children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                            href: `/item/${item.id}`,
                            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].cardLink,
                            children: [
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                    src: (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["getImageUrl"])(item.item_image),
                                    alt: item.name
                                }, void 0, false, {
                                    fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                    lineNumber: 86,
                                    columnNumber: 15
                                }, this),
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                    children: [
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                            children: item.name
                                        }, void 0, false, {
                                            fileName: "[project]/src/app/shops/[shop_code]/page.tsx",
                                            lineNumber: 88,
                                            columnNumber: 17
                                        }, this),
                                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
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
            !isAuthenticated && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$shops$2f5b$shop_code$5d2f$W$2d$Shop$2d$Home$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].notice,
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
_s(ShopHomePage, "xdh5hHqnbuIR2zFCBY7i+XtGvrc=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useParams"],
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemListByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemListByShopSWR"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$services$2f$useItemSearchByShopSWR$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useItemSearchByShopSWR"]
    ];
});
_c = ShopHomePage;
var _c;
__turbopack_context__.k.register(_c, "ShopHomePage");
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
]);

//# sourceMappingURL=src_b597fcb2._.js.map