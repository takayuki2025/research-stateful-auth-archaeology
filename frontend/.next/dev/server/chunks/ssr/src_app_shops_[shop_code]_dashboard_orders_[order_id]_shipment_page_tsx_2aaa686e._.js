module.exports = [
"[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ShopOrderShipmentPage,
    "dynamic",
    ()=>dynamic
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react-jsx-dev-runtime.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
"use client";
;
;
;
;
const dynamic = "force-dynamic";
function ShopOrderShipmentPage() {
    const { shop_code, order_id } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useParams"])();
    const router = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useRouter"])();
    const { apiClient, isReady } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const [shipment, setShipment] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(null);
    const [isLoading, setIsLoading] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(true);
    const [isActionLoading, setIsActionLoading] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(false);
    /**
   * ============================
   * Fetch
   * ============================
   */ const fetchShipment = async ()=>{
        if (!apiClient) return;
        setIsLoading(true);
        try {
            const res = await apiClient.get(`/shops/${shop_code}/dashboard/orders/${order_id}/shipment`);
            const raw = res.data;
            setShipment({
                id: raw.shipment_id ?? null,
                status: raw.shipment_id ? raw.status : null,
                eta: raw.eta ?? null,
                deliveredAt: raw.delivered_at ?? null,
                nextAction: raw.next_action ?? null
            });
        } finally{
            setIsLoading(false);
        }
    };
    (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useEffect"])(()=>{
        if (!isReady || !apiClient) return;
        fetchShipment();
    }, [
        isReady,
        apiClient
    ]);
    if (isLoading) return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: "p-6",
        children: "読み込み中..."
    }, void 0, false, {
        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
        lineNumber: 78,
        columnNumber: 25
    }, this);
    if (!shipment) return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: "p-6 text-red-600",
        children: "取得失敗"
    }, void 0, false, {
        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
        lineNumber: 79,
        columnNumber: 25
    }, this);
    const hasShipment = shipment.id !== null;
    /**
   * ============================
   * Action
   * ============================
   */ const handleAction = async ()=>{
        if (!apiClient || !shipment.nextAction) return;
        setIsActionLoading(true);
        try {
            const action = shipment.nextAction.key;
            // 🔒 accept は「未作成」のときだけ
            if (action === "accept" && hasShipment) {
                return;
            }
            const url = action === "accept" ? `/shops/${shop_code}/dashboard/orders/${order_id}/shipment` : `/shipments/${shipment.id}/${action}`;
            await apiClient.post(url);
            await fetchShipment();
        } finally{
            setIsActionLoading(false);
        }
    };
    /**
   * ============================
   * Render
   * ============================
   */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: "p-6 space-y-4",
        children: [
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("h1", {
                className: "text-xl font-bold",
                children: [
                    "配送管理（注文 #",
                    order_id,
                    "）"
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 119,
                columnNumber: 7
            }, this),
            !hasShipment && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: "text-gray-600",
                children: "まだ配送は作成されていません。"
            }, void 0, false, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 122,
                columnNumber: 9
            }, this),
            hasShipment && shipment.status && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: "text-sm",
                children: [
                    "状態：",
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                        className: "ml-2 font-mono",
                        children: shipment.status
                    }, void 0, false, {
                        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                        lineNumber: 128,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 126,
                columnNumber: 9
            }, this),
            shipment.eta && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: "text-sm",
                children: [
                    "到着予定：",
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("span", {
                        className: "ml-2",
                        children: shipment.eta
                    }, void 0, false, {
                        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                        lineNumber: 134,
                        columnNumber: 16
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 133,
                columnNumber: 9
            }, this),
            shipment.status === "delivered" && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: "mt-4 space-y-1 text-sm",
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: "font-semibold text-green-700",
                        children: "配達完了"
                    }, void 0, false, {
                        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                        lineNumber: 141,
                        columnNumber: 11
                    }, this),
                    shipment.deliveredAt && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: "text-gray-600",
                        children: [
                            "配達完了日：",
                            new Date(shipment.deliveredAt).toLocaleString()
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                        lineNumber: 144,
                        columnNumber: 13
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 140,
                columnNumber: 9
            }, this),
            shipment.nextAction && !(shipment.nextAction.key === "accept" && hasShipment) && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                disabled: isActionLoading,
                onClick: handleAction,
                className: "px-4 py-2 border rounded bg-blue-600 text-white",
                children: shipment.nextAction.label
            }, void 0, false, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 155,
                columnNumber: 11
            }, this),
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                onClick: ()=>router.push(`/shops/${shop_code}/dashboard/orders`),
                className: "text-blue-600 underline text-sm",
                children: "← 注文一覧へ戻る"
            }, void 0, false, {
                fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
                lineNumber: 164,
                columnNumber: 7
            }, this)
        ]
    }, void 0, true, {
        fileName: "[project]/src/app/shops/[shop_code]/dashboard/orders/[order_id]/shipment/page.tsx",
        lineNumber: 118,
        columnNumber: 5
    }, this);
}
}),
];

//# sourceMappingURL=src_app_shops_%5Bshop_code%5D_dashboard_orders_%5Border_id%5D_shipment_page_tsx_2aaa686e._.js.map