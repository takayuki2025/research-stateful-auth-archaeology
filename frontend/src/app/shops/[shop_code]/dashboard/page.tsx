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
        ["owner", "manager", "staff"].includes(r.role)
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

      <div className="grid gap-4 md:grid-cols-2">
        {/* 商品管理 */}
        <Link
          href={`/shops/${shop_code}/dashboard/items`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          商品管理
        </Link>

        {/* 注文管理 */}
        <Link
          href={`/shops/${shop_code}/dashboard/orders`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          注文・配送管理
        </Link>

        {/* 店舗設定 */}
        <Link
          href={`/shops/${shop_code}/dashboard/settings`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          店舗設定
        </Link>

        {/* ===== AtlasKernel v3 ===== */}
        <div className="p-4 border rounded space-y-3 bg-yellow-50 border-yellow-300">
          <div className="flex items-center justify-between">
            <h2 className="font-semibold text-lg">Atlas 分析レビュー（v3）</h2>

            {/* 🟡 未決定バッジ（将来 API 連携で動的化） */}
            <span className="text-xs px-2 py-1 rounded bg-yellow-500 text-white">
              要対応
            </span>
          </div>

          <p className="text-sm text-gray-600">
            AI解析結果の確認・判断・再解析を行います。
          </p>

          <div className="flex flex-wrap gap-3 text-sm">
            {/* 一覧 */}
            <Link
              href={`/shops/${shop_code}/dashboard/atlas/requests`}
              className="text-blue-600 underline"
            >
              ▶ レビュー一覧
            </Link>

            {/* 履歴（Ledger） */}
            <Link
              href={`/shops/${shop_code}/dashboard/atlas/history`}
              className="text-gray-700 underline"
            >
              📜 判断履歴
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
