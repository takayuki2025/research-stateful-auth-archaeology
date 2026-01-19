"use client";

import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { useAuth } from "@/ui/auth/AuthProvider";

export default function ShopDashboardPage() {
  const { shop_code } = useParams<{ shop_code: string }>();
  const router = useRouter();

  const {
    user,
    isAuthenticated,
    isLoading: isAuthLoading,
    authReady,
  } = useAuth();

  if (!authReady || isAuthLoading) {
    return <div className="p-6">読み込み中...</div>;
  }

  if (!isAuthenticated) {
    router.replace("/login");
    return null;
  }

  const isShopStaff =
    user?.shop_roles?.some(
      (r) =>
        r.shop_code === shop_code &&
        ["owner", "manager", "staff"].includes(r.role),
    ) ?? false;

  if (!isShopStaff) {
    return <div className="p-6">アクセス権限がありません。</div>;
  }

  return (
    <div className="p-6 space-y-6">
      <Link href={`/shops/${shop_code}`} className="text-blue-600 underline">
        ← 店舗トップへ戻る
      </Link>

      <h1 className="text-3xl font-bold">店舗ダッシュボード</h1>

      {/* 2列×3行（=合計6枠） */}
      <div className="grid gap-4 md:grid-cols-2">
        {/* ===== Row 1 ===== */}
        {/* 商品管理（ダミーOK） */}
        <Link
          href={`/shops/${shop_code}/dashboard/items`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          商品管理
          <p className="text-xs text-gray-500 mt-1">※ </p>
        </Link>

        {/* 注文・配送管理（現状維持） */}
        <Link
          href={`/shops/${shop_code}/dashboard/orders`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          注文・配送管理
          <p className="text-xs text-gray-500 mt-1">※ </p>
        </Link>

        {/* ===== Row 2 ===== */}
        {/* 顧客管理（ダミー） */}
        <Link
          href={`/shops/${shop_code}/dashboard/customers`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          顧客管理
          <p className="text-xs text-gray-500 mt-1">※ </p>
        </Link>

        {/* 店舗設定（ダミー） */}
        <Link
          href={`/shops/${shop_code}/dashboard/settings`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          店舗設定
          <p className="text-xs text-gray-500 mt-1">※ </p>
        </Link>

        {/* ===== Row 3 ===== */}
        {/* Atlaskernel System 解析レビュー（v3）（現状維持） */}
        <div className="p-4 border rounded space-y-3 bg-yellow-50 border-yellow-300">
          <div className="flex items-center justify-between">
            <h2 className="font-semibold text-lg">
              AtlaskernelSystem 解析レビュー（v3）
            </h2>

            <span className="text-xs px-2 py-1 rounded bg-yellow-500 text-white">
              テスト 管理者用
            </span>
          </div>

          <p className="text-sm text-gray-600">
            AI解析結果の確認・判断・再解析を行います。
          </p>

          <div className="flex flex-wrap gap-3 text-sm">
            <Link
              href={`/shops/${shop_code}/dashboard/atlas/requests`}
              className="text-blue-600 underline"
            >
              ▶ レビュー一覧
              <p className="text-xs text-gray-500">
                ※ 判断履歴は各レビュー詳細画面から確認できます
              </p>
            </Link>
          </div>
        </div>

        {/* TrustLedger PaymentSystem 管理レビュー（v3）（新規リンク） */}
        <div className="p-4 border rounded space-y-3 bg-sky-50 border-sky-300">
          <div className="flex items-center justify-between">
            <h2 className="font-semibold text-lg">
              TrustLedger PaymentSystem 管理レビュー（v3）
            </h2>

            <span className="text-xs px-2 py-1 rounded bg-sky-600 text-white">
              Finance / Ledger
            </span>
          </div>

          <p className="text-sm text-gray-600">
            購入後の台帳・残高・ホールド・出金の監査と運用を行います。
          </p>

          <div className="flex flex-wrap gap-3 text-sm">
            {/* 新規導線：まずは一覧/概要ページへ（詳細設計） */}
            <Link
              href={`/shops/${shop_code}/dashboard/trustledger`}
              className="text-blue-600 underline"
            >
              ▶ 管理レビュー一覧
              <p className="text-xs text-gray-500">※</p>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
