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
        <Link
          href={`/shops/${shop_code}/dashboard/items`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          商品管理
        </Link>

        <Link
          href={`/shops/${shop_code}/dashboard/orders`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          注文・配送管理
        </Link>

        <Link
          href={`/shops/${shop_code}/dashboard/settings`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          店舗設定
        </Link>

        <Link
          href={`/shops/${shop_code}/dashboard/atlas/requests`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          Atlas 分析リクエスト管理
        </Link>

        <Link
          href={`/shops/${shop_code}/dashboard/atlas/playground`}
          className="p-4 border rounded hover:bg-gray-50"
        >
          Atlas Playground（検証）
        </Link>
      </div>
    </div>
  );
}
