(globalThis.TURBOPACK || (globalThis.TURBOPACK = [])).push([typeof document === "object" ? document.currentScript : undefined,
"[project]/src/app/thanks/buy/konbini/W-ThanksKonbini.module.css [app-client] (css module)", ((__turbopack_context__) => {

__turbopack_context__.v({
  "actions": "W-ThanksKonbini-module__fhXwUa__actions",
  "backHomeLink": "W-ThanksKonbini-module__fhXwUa__backHomeLink",
  "codeBlock": "W-ThanksKonbini-module__fhXwUa__codeBlock",
  "konbiniInfo": "W-ThanksKonbini-module__fhXwUa__konbiniInfo",
  "message": "W-ThanksKonbini-module__fhXwUa__message",
  "messageBox": "W-ThanksKonbini-module__fhXwUa__messageBox",
  "thankYouPage": "W-ThanksKonbini-module__fhXwUa__thankYouPage",
  "title": "W-ThanksKonbini-module__fhXwUa__title",
});
}),
"[project]/src/app/thanks/buy/konbini/page.tsx [app-client] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ThanksBuyKonbiniPage
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/jsx-dev-runtime.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/compiled/react/index.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/client/app-dir/link.js [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-client] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/thanks/buy/konbini/W-ThanksKonbini.module.css [app-client] (css module)");
;
var _s = __turbopack_context__.k.signature();
"use client";
;
;
;
;
;
function ThanksBuyKonbiniPage() {
    _s();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const orderId = searchParams.get("order_id");
    const { apiClient } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"])();
    const [order, setOrder] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])(null);
    const [error, setError] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useState"])(null);
    const retryTimerRef = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRef"])(null);
    const retryCountRef = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useRef"])(0);
    const MAX_RETRY = 30;
    const INTERVAL_MS = 1000;
    /* =======================
     Fetch with polling
  ======================= */ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$index$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useEffect"])({
        "ThanksBuyKonbiniPage.useEffect": ()=>{
            if (!apiClient || !orderId) {
                setError("注文情報が取得できませんでした。");
                return;
            }
            let cancelled = false;
            const fetchOrder = {
                "ThanksBuyKonbiniPage.useEffect.fetchOrder": async ()=>{
                    try {
                        const res = await apiClient.get(`/me/orders/${orderId}`);
                        if (cancelled) return;
                        setOrder(res.data);
                        const payment = res.data.payment;
                        // polling 継続条件
                        if (payment && payment.method === "konbini" && payment.status === "requires_action" && !payment.instructions && retryCountRef.current < MAX_RETRY) {
                            retryCountRef.current += 1;
                            retryTimerRef.current = setTimeout(fetchOrder, INTERVAL_MS);
                        }
                    } catch  {
                        if (!cancelled) {
                            setError("注文情報の取得に失敗しました。");
                        }
                    }
                }
            }["ThanksBuyKonbiniPage.useEffect.fetchOrder"];
            fetchOrder();
            return ({
                "ThanksBuyKonbiniPage.useEffect": ()=>{
                    cancelled = true;
                    if (retryTimerRef.current) {
                        clearTimeout(retryTimerRef.current);
                    }
                }
            })["ThanksBuyKonbiniPage.useEffect"];
        }
    }["ThanksBuyKonbiniPage.useEffect"], [
        apiClient,
        orderId
    ]);
    /* =======================
     Guards
  ======================= */ if (error) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].thankYouPage,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].messageBox,
                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].message,
                    children: error
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 111,
                    columnNumber: 11
                }, this)
            }, void 0, false, {
                fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                lineNumber: 110,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
            lineNumber: 109,
            columnNumber: 7
        }, this);
    }
    if (!order || !order.payment) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].thankYouPage,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].messageBox,
                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].message,
                    children: "支払い情報を生成中です…"
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 121,
                    columnNumber: 11
                }, this)
            }, void 0, false, {
                fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                lineNumber: 120,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
            lineNumber: 119,
            columnNumber: 7
        }, this);
    }
    const payment = order.payment;
    /* =======================
     支払い完了
  ======================= */ if (payment.status === "succeeded") {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].thankYouPage,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].messageBox,
                children: [
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h1", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].title,
                        children: "お支払いが完了しました"
                    }, void 0, false, {
                        fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                        lineNumber: 137,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                        href: "/mypage?page=buy",
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].backHomeLink,
                        children: "注文履歴へ"
                    }, void 0, false, {
                        fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                        lineNumber: 138,
                        columnNumber: 11
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                lineNumber: 136,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
            lineNumber: 135,
            columnNumber: 7
        }, this);
    }
    /* =======================
     支払い待ち
  ======================= */ if (!payment.instructions) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].thankYouPage,
            children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].messageBox,
                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].message,
                    children: [
                        "支払い情報を生成中です…",
                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("br", {}, void 0, false, {
                            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                            lineNumber: 156,
                            columnNumber: 13
                        }, this),
                        "数秒後に自動で反映されます。"
                    ]
                }, void 0, true, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 154,
                    columnNumber: 11
                }, this)
            }, void 0, false, {
                fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                lineNumber: 153,
                columnNumber: 9
            }, this)
        }, void 0, false, {
            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
            lineNumber: 152,
            columnNumber: 7
        }, this);
    }
    const instructions = payment.instructions;
    const expiresAtText = instructions.expires_at ? new Date(instructions.expires_at * 1000).toLocaleString("ja-JP") : "未設定";
    const confirmationNumber = instructions.store && Object.values(instructions.store)[0]?.confirmation_number ? Object.values(instructions.store)[0].confirmation_number : "未発行（後ほど表示されます）";
    const availableStores = instructions.store ? Object.keys(instructions.store) : [];
    /* =======================
     UI
  ======================= */ return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].thankYouPage,
        children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].messageBox,
            children: [
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("h1", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].title,
                    children: "現在、コンビニ支払い待ちです。"
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 187,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].message,
                    children: "支払期限までに指定のコンビニでお支払いください。"
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 189,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].message,
                    children: "お支払い完了後、自動的に注文が確定し発送準備に入ります。"
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 192,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].konbiniInfo,
                    children: [
                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                            children: [
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("strong", {
                                    children: "支払期限："
                                }, void 0, false, {
                                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                    lineNumber: 198,
                                    columnNumber: 13
                                }, this),
                                expiresAtText
                            ]
                        }, void 0, true, {
                            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                            lineNumber: 197,
                            columnNumber: 11
                        }, this),
                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                            children: [
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("strong", {
                                    children: "受付番号："
                                }, void 0, false, {
                                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                    lineNumber: 203,
                                    columnNumber: 13
                                }, this),
                                confirmationNumber
                            ]
                        }, void 0, true, {
                            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                            lineNumber: 202,
                            columnNumber: 11
                        }, this),
                        availableStores.length > 0 && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["Fragment"], {
                            children: [
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                                    children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("strong", {
                                        children: "利用可能なコンビニ："
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                        lineNumber: 210,
                                        columnNumber: 17
                                    }, this)
                                }, void 0, false, {
                                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                    lineNumber: 209,
                                    columnNumber: 15
                                }, this),
                                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("ul", {
                                    children: availableStores.map((store)=>/*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("li", {
                                            children: store
                                        }, store, false, {
                                            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                            lineNumber: 214,
                                            columnNumber: 19
                                        }, this))
                                }, void 0, false, {
                                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                                    lineNumber: 212,
                                    columnNumber: 15
                                }, this)
                            ]
                        }, void 0, true)
                    ]
                }, void 0, true, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 196,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].actions,
                    children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$compiled$2f$react$2f$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["jsxDEV"])(__TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$client$2f$app$2d$dir$2f$link$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["default"], {
                        href: `/mypage/orders/${orderId}`,
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$thanks$2f$buy$2f$konbini$2f$W$2d$ThanksKonbini$2e$module$2e$css__$5b$app$2d$client$5d$__$28$css__module$29$__["default"].backHomeLink,
                        children: "注文履歴へ"
                    }, void 0, false, {
                        fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                        lineNumber: 222,
                        columnNumber: 11
                    }, this)
                }, void 0, false, {
                    fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
                    lineNumber: 221,
                    columnNumber: 9
                }, this)
            ]
        }, void 0, true, {
            fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
            lineNumber: 186,
            columnNumber: 7
        }, this)
    }, void 0, false, {
        fileName: "[project]/src/app/thanks/buy/konbini/page.tsx",
        lineNumber: 185,
        columnNumber: 5
    }, this);
}
_s(ThanksBuyKonbiniPage, "oH3FqmFvY2cICNrYMOpSxN/nYow=", false, function() {
    return [
        __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useSearchParams"],
        __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$client$5d$__$28$ecmascript$29$__["useAuth"]
    ];
});
_c = ThanksBuyKonbiniPage;
var _c;
__turbopack_context__.k.register(_c, "ThanksBuyKonbiniPage");
if (typeof globalThis.$RefreshHelpers$ === 'object' && globalThis.$RefreshHelpers !== null) {
    __turbopack_context__.k.registerExports(__turbopack_context__.m, globalThis.$RefreshHelpers$);
}
}),
]);

//# sourceMappingURL=src_app_thanks_buy_konbini_3774f7dd._.js.map