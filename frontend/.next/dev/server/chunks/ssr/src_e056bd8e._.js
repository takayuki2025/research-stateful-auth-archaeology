module.exports = [
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
"[project]/src/app/mypage/profile/W-ProfilePage.module.css [app-ssr] (css module)", ((__turbopack_context__) => {

__turbopack_context__.v({
  "alert-success2": "W-ProfilePage-module__ZcCojq__alert-success2",
  "email_form": "W-ProfilePage-module__ZcCojq__email_form",
  "form-group": "W-ProfilePage-module__ZcCojq__form-group",
  "form-wrapper": "W-ProfilePage-module__ZcCojq__form-wrapper",
  "image_button_row": "W-ProfilePage-module__ZcCojq__image_button_row",
  "image_name": "W-ProfilePage-module__ZcCojq__image_name",
  "item_sell_contents_box_line": "W-ProfilePage-module__ZcCojq__item_sell_contents_box_line",
  "label_form_1": "W-ProfilePage-module__ZcCojq__label_form_1",
  "label_form_2": "W-ProfilePage-module__ZcCojq__label_form_2",
  "label_form_3": "W-ProfilePage-module__ZcCojq__label_form_3",
  "label_form_4": "W-ProfilePage-module__ZcCojq__label_form_4",
  "login_page": "W-ProfilePage-module__ZcCojq__login_page",
  "name_form": "W-ProfilePage-module__ZcCojq__name_form",
  "password_form": "W-ProfilePage-module__ZcCojq__password_form",
  "profile__error": "W-ProfilePage-module__ZcCojq__profile__error",
  "submit": "W-ProfilePage-module__ZcCojq__submit",
  "submit_form": "W-ProfilePage-module__ZcCojq__submit_form",
  "title": "W-ProfilePage-module__ZcCojq__title",
  "upload_submit": "W-ProfilePage-module__ZcCojq__upload_submit",
  "user_image_css": "W-ProfilePage-module__ZcCojq__user_image_css",
  "user_image_error_message": "W-ProfilePage-module__ZcCojq__user_image_error_message",
});
}),
"[project]/src/app/mypage/profile/page.tsx [app-ssr] (ecmascript)", ((__turbopack_context__) => {
"use strict";

__turbopack_context__.s([
    "default",
    ()=>ProfilePage
]);
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react-jsx-dev-runtime.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/dist/server/route-modules/app-page/vendored/ssr/react.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/node_modules/next/navigation.js [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/ui/auth/useAuth.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__ = __turbopack_context__.i("[project]/src/utils/utils.ts [app-ssr] (ecmascript)");
var __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__ = __turbopack_context__.i("[project]/src/app/mypage/profile/W-ProfilePage.module.css [app-ssr] (css module)");
"use client";
;
;
;
;
;
;
function ProfilePage() {
    const router = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useRouter"])();
    const searchParams = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$navigation$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useSearchParams"])();
    const { user: authUser, apiClient, isAuthenticated, isLoading: isAuthLoading, logout, reloadUser } = (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$ui$2f$auth$2f$useAuth$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useAuth"])();
    const isVerificationRedirect = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>searchParams.get("verified") === "true", [
        searchParams
    ]);
    const [profileUser, setProfileUser] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(null);
    const [form, setForm] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])({
        display_name: "",
        post_number: "",
        address: "",
        building: ""
    });
    const [profileErrors, setProfileErrors] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])({});
    const [imageError, setImageError] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])("");
    const [successMessage, setSuccessMessage] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])("");
    const [isLoading, setIsLoading] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(true);
    const [isFetching, setIsFetching] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(false);
    const [isRecovering, setIsRecovering] = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useState"])(false);
    const verificationHandledRef = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useRef"])(false);
    const fileInputRef = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useRef"])(null);
    const profileImageUrl = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useMemo"])(()=>{
        return (0, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["getImageUrl"])(profileUser?.user_image ?? null, __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$utils$2f$utils$2e$ts__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["IMAGE_TYPE"].USER, Date.now());
    }, [
        profileUser?.user_image
    ]);
    const initializeProfileFromResponse = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useCallback"])((src)=>{
        const data = src?.user ?? src;
        setProfileUser(data);
        setForm({
            display_name: data.display_name ?? "",
            post_number: data.post_number ?? "",
            address: data.address ?? "",
            building: data.building ?? ""
        });
    }, []);
    const fetchUserProfile = (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useCallback"])(async (isRetry = false)=>{
        if (!apiClient) return;
        if (!isRetry) {
            setIsFetching(true);
            setSuccessMessage("");
            setProfileErrors({});
        }
        try {
            const res = await apiClient.get("/mypage/profile");
            initializeProfileFromResponse(res.data);
            setIsLoading(false);
            setIsRecovering(false);
        } catch (err) {
            const axiosErr = err;
            const status = axiosErr.response?.status;
            if (status === 401) {
                // 認証切れ → ログアウトしてログインページへ
                await logout();
                router.replace("/login");
                return;
            }
            setIsLoading(false);
        } finally{
            if (!isRetry) setIsFetching(false);
        }
    }, [
        apiClient,
        initializeProfileFromResponse,
        logout,
        router
    ]);
    // メール認証完了後の再同期（verified=true で遷移してきた場合）
    (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useEffect"])(()=>{
        if (!isVerificationRedirect) return;
        if (verificationHandledRef.current) return;
        verificationHandledRef.current = true;
        const run = async ()=>{
            try {
                setIsRecovering(true);
                await reloadUser();
            } finally{
                setIsRecovering(false);
            }
        };
        run();
    }, [
        isVerificationRedirect,
        reloadUser
    ]);
    // 認証状態 & apiClient が揃ったらプロフィール取得
    (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["useEffect"])(()=>{
        if (isAuthLoading || isRecovering) return;
        if (!isAuthenticated || !apiClient) {
            router.replace("/login");
            return;
        }
        if (!profileUser && !isFetching) {
            fetchUserProfile();
        }
    }, [
        isAuthLoading,
        isRecovering,
        isAuthenticated,
        apiClient,
        profileUser,
        isFetching,
        fetchUserProfile,
        router
    ]);
    // プロフィール画像アップロード
    const handleImageUpload = async (e)=>{
        const file = e.target.files?.[0];
        if (!file || !apiClient) return;
        setImageError("");
        setIsLoading(true);
        const formData = new FormData();
        formData.append("user_image", file);
        try {
            const res = await apiClient.post("/mypage/profile/image", formData, {
                headers: {
                    "Content-Type": "multipart/form-data"
                }
            });
            initializeProfileFromResponse(res.data);
            setSuccessMessage("画像を更新しました！");
        } catch (err) {
            const msg = err?.response?.data?.errors?.user_image?.[0] ?? "画像アップロードに失敗しました。";
            setImageError(msg);
        } finally{
            setIsLoading(false);
            if (fileInputRef.current) fileInputRef.current.value = "";
        }
    };
    // プロフィール更新
    const handleProfileUpdate = async (e)=>{
        e.preventDefault();
        if (!apiClient) return;
        setProfileErrors({});
        setIsLoading(true);
        try {
            const res = profileUser ? await apiClient.patch("/mypage/profile", form) // 既存 → 更新
             : await apiClient.post("/mypage/profile", form); // 初回 → 作成
            initializeProfileFromResponse(res.data);
            setSuccessMessage(profileUser ? "プロフィールを更新しました！" : "プロフィールを作成しました！");
        } catch (err) {
            const status = err.response?.status;
            if (status === 422) {
                setProfileErrors(err.response?.data?.errors ?? {});
            } else if (status === 401) {
                await logout();
                router.replace("/login");
            } else {
                setSuccessMessage("更新時にエラーが発生しました。");
            }
        } finally{
            setIsLoading(false);
        }
    };
    // ローディング状態
    if (isAuthLoading || isLoading || isRecovering) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].login_page} max-w-[1400px] mx-auto pt-5 pb-10`,
            children: [
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].title,
                    children: "プロフィール設定"
                }, void 0, false, {
                    fileName: "[project]/src/app/mypage/profile/page.tsx",
                    lineNumber: 241,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                    className: "text-center p-8",
                    children: [
                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                            className: "animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-red-500 mx-auto"
                        }, void 0, false, {
                            fileName: "[project]/src/app/mypage/profile/page.tsx",
                            lineNumber: 243,
                            columnNumber: 11
                        }, this),
                        /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                            className: "text-gray-500 mt-3",
                            children: isRecovering ? "セッションを再同期しています..." : "読み込み中..."
                        }, void 0, false, {
                            fileName: "[project]/src/app/mypage/profile/page.tsx",
                            lineNumber: 244,
                            columnNumber: 11
                        }, this)
                    ]
                }, void 0, true, {
                    fileName: "[project]/src/app/mypage/profile/page.tsx",
                    lineNumber: 242,
                    columnNumber: 9
                }, this)
            ]
        }, void 0, true, {
            fileName: "[project]/src/app/mypage/profile/page.tsx",
            lineNumber: 240,
            columnNumber: 7
        }, this);
    }
    // 認証エラー
    if (!isAuthenticated || !profileUser) {
        return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
            className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].login_page} max-w-[1400px] mx-auto pt-5 pb-10`,
            children: [
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].title,
                    children: "プロフィール設定"
                }, void 0, false, {
                    fileName: "[project]/src/app/mypage/profile/page.tsx",
                    lineNumber: 256,
                    columnNumber: 9
                }, this),
                /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("p", {
                    children: "認証エラーが発生しました。ログインし直してください。"
                }, void 0, false, {
                    fileName: "[project]/src/app/mypage/profile/page.tsx",
                    lineNumber: 257,
                    columnNumber: 9
                }, this)
            ]
        }, void 0, true, {
            fileName: "[project]/src/app/mypage/profile/page.tsx",
            lineNumber: 255,
            columnNumber: 7
        }, this);
    }
    return /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
        className: `${__TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].login_page} max-w-[1400px] mx-auto pt-5 pb-10`,
        children: [
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("h2", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].title,
                children: "プロフィール設定"
            }, void 0, false, {
                fileName: "[project]/src/app/mypage/profile/page.tsx",
                lineNumber: 267,
                columnNumber: 7
            }, this),
            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["form-wrapper"],
                children: [
                    successMessage && /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["alert-success2"],
                        children: successMessage
                    }, void 0, false, {
                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                        lineNumber: 271,
                        columnNumber: 11
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("form", {
                        onSubmit: (e)=>e.preventDefault(),
                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].item_sell_contents_box_line,
                        children: [
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].image_name,
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].image_button_row,
                                        children: [
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("img", {
                                                src: profileImageUrl,
                                                alt: "プロフィール画像",
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].user_image_css
                                            }, profileUser.user_image || "default", false, {
                                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                                lineNumber: 281,
                                                columnNumber: 15
                                            }, this),
                                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("button", {
                                                type: "button",
                                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].upload_submit,
                                                onClick: ()=>fileInputRef.current?.click(),
                                                disabled: isLoading,
                                                children: "画像を選択する"
                                            }, void 0, false, {
                                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                                lineNumber: 287,
                                                columnNumber: 15
                                            }, this)
                                        ]
                                    }, void 0, true, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 280,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                        type: "file",
                                        name: "user_image",
                                        ref: fileInputRef,
                                        style: {
                                            display: "none"
                                        },
                                        onChange: handleImageUpload,
                                        accept: "image/*"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 297,
                                        columnNumber: 13
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 279,
                                columnNumber: 11
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].user_image_error_message,
                                children: imageError
                            }, void 0, false, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 307,
                                columnNumber: 11
                            }, this)
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                        lineNumber: 275,
                        columnNumber: 9
                    }, this),
                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("form", {
                        onSubmit: handleProfileUpdate,
                        children: [
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["form-group"],
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("label", {
                                        htmlFor: "name",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].label_form_1,
                                        children: "ユーザー名"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 314,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                        id: "display_name",
                                        name: "display_name",
                                        type: "text",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].name_form,
                                        value: form.display_name,
                                        onChange: (e)=>setForm((prev)=>({
                                                    ...prev,
                                                    display_name: e.target.value
                                                }))
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 317,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].profile__error,
                                        children: profileErrors.name ? profileErrors.name[0] : ""
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 327,
                                        columnNumber: 13
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 313,
                                columnNumber: 11
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["form-group"],
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("label", {
                                        htmlFor: "post_number",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].label_form_2,
                                        children: "郵便番号 (8桁、ハイフンあり)"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 334,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                        id: "post_number",
                                        type: "text",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].email_form,
                                        name: "post_number",
                                        value: form.post_number,
                                        onChange: (e)=>setForm((prev)=>({
                                                    ...prev,
                                                    post_number: e.target.value
                                                })),
                                        placeholder: "例: 100-0001",
                                        maxLength: 8
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 337,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].profile__error,
                                        children: profileErrors.post_number ? profileErrors.post_number[0] : ""
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 349,
                                        columnNumber: 13
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 333,
                                columnNumber: 11
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["form-group"],
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("label", {
                                        htmlFor: "address",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].label_form_3,
                                        children: "住所"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 356,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                        id: "address",
                                        type: "text",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].password_form,
                                        name: "address",
                                        value: form.address,
                                        onChange: (e)=>setForm((prev)=>({
                                                    ...prev,
                                                    address: e.target.value
                                                })),
                                        placeholder: "手動で入力してください"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 359,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].profile__error,
                                        children: profileErrors.address ? profileErrors.address[0] : ""
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 370,
                                        columnNumber: 13
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 355,
                                columnNumber: 11
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"]["form-group"],
                                children: [
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("label", {
                                        htmlFor: "building",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].label_form_4,
                                        children: "建物名"
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 377,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                        id: "building",
                                        type: "text",
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].password_form,
                                        name: "building",
                                        value: form.building,
                                        onChange: (e)=>setForm((prev)=>({
                                                    ...prev,
                                                    building: e.target.value
                                                }))
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 380,
                                        columnNumber: 13
                                    }, this),
                                    /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                        className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].profile__error,
                                        children: profileErrors.building ? profileErrors.building[0] : ""
                                    }, void 0, false, {
                                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                                        lineNumber: 390,
                                        columnNumber: 13
                                    }, this)
                                ]
                            }, void 0, true, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 376,
                                columnNumber: 11
                            }, this),
                            /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("div", {
                                className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].submit,
                                children: /*#__PURE__*/ (0, __TURBOPACK__imported__module__$5b$project$5d2f$node_modules$2f$next$2f$dist$2f$server$2f$route$2d$modules$2f$app$2d$page$2f$vendored$2f$ssr$2f$react$2d$jsx$2d$dev$2d$runtime$2e$js__$5b$app$2d$ssr$5d$__$28$ecmascript$29$__["jsxDEV"])("input", {
                                    type: "submit",
                                    className: __TURBOPACK__imported__module__$5b$project$5d2f$src$2f$app$2f$mypage$2f$profile$2f$W$2d$ProfilePage$2e$module$2e$css__$5b$app$2d$ssr$5d$__$28$css__module$29$__["default"].submit_form,
                                    value: "更新する",
                                    disabled: isLoading
                                }, void 0, false, {
                                    fileName: "[project]/src/app/mypage/profile/page.tsx",
                                    lineNumber: 396,
                                    columnNumber: 13
                                }, this)
                            }, void 0, false, {
                                fileName: "[project]/src/app/mypage/profile/page.tsx",
                                lineNumber: 395,
                                columnNumber: 11
                            }, this)
                        ]
                    }, void 0, true, {
                        fileName: "[project]/src/app/mypage/profile/page.tsx",
                        lineNumber: 311,
                        columnNumber: 9
                    }, this)
                ]
            }, void 0, true, {
                fileName: "[project]/src/app/mypage/profile/page.tsx",
                lineNumber: 269,
                columnNumber: 7
            }, this)
        ]
    }, authUser?.id || "unauthenticated", true, {
        fileName: "[project]/src/app/mypage/profile/page.tsx",
        lineNumber: 263,
        columnNumber: 5
    }, this);
}
}),
];

//# sourceMappingURL=src_e056bd8e._.js.map