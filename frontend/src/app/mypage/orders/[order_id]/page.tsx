"use client";

import { useParams, useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { useAuth } from "@/ui/auth/useAuth";

/**
 * ============================
 * Types
 * ============================
 */
type OrderDetail = {
  order_id: number;
  order_status: string;
  total_amount: number;
  currency: string;

  address_snapshot_at: string | null;

  shipping_address: {
    postal_code?: string;
    prefecture?: string;
    city?: string;
    address_line1?: string;
    address_line2?: string;
    recipient_name?: string;
    phone?: string;
  } | null;

  payment: {
    method: string;
    status: string;
    provider_payment_id?: string;
  } | null;

  shipment: {
    shipment_id: number;
    status: string;
    eta: string | null;
    delivered_at?: string | null; // ★ 追加（重要）
  } | null;
};

export default function OrderDetailPage() {
  const { apiClient, isReady } = useAuth();
  const { order_id } = useParams<{ order_id: string }>();
  const router = useRouter();

  const [order, setOrder] = useState<OrderDetail | null>(null);

  /**
   * ============================
   * Fetch
   * ============================
   */
  useEffect(() => {
    if (!isReady || !apiClient || !order_id) return;

    apiClient
      .get(`/me/orders/${order_id}`)
      .then((res) => setOrder(res.data))
      .catch((e) => {
        if (e.response?.status === 404 || e.response?.status === 403) {
          router.replace("/mypage?page=buy");
        }
      });
  }, [isReady, apiClient, order_id, router]);

  if (!order) return <div className="p-6">読み込み中...</div>;

  const isDelivered = order.shipment?.status === "delivered";

  /**
   * ============================
   * Render
   * ============================
   */
  return (
    <div className="p-6 space-y-6 max-w-2xl mx-auto">
      <h1 className="text-xl font-bold">注文 #{order.order_id}</h1>

      {/* 注文概要 */}
      <div className="border rounded p-4 space-y-1">
        <div>注文状態：{order.order_status}</div>
        <div>
          合計金額：¥{order.total_amount.toLocaleString()} {order.currency}
        </div>
        <div>
          購入日：
          {order.address_snapshot_at
            ? new Date(order.address_snapshot_at).toLocaleString()
            : "不明"}
        </div>
      </div>

      {/* 配送状況 */}
      <div className="border rounded p-4 space-y-2">
        <h2 className="font-semibold">配送状況</h2>

        {order.shipment ? (
          <>
            <div className="flex items-center gap-2">
              <span>状態：{order.shipment.status}</span>

              {isDelivered && (
                <span className="text-green-700 text-sm font-semibold">
                  配達完了
                </span>
              )}
            </div>

            <div>到着予定：{order.shipment.eta ?? "未定"}</div>

            {/* ★ delivered_at 表示（ここが欠けていた） */}
            {isDelivered && order.shipment.delivered_at && (
              <div className="text-sm text-gray-600">
                配達完了日：
                {new Date(order.shipment.delivered_at).toLocaleString()}
              </div>
            )}
          </>
        ) : (
          <div>配送準備中</div>
        )}
      </div>

      {/* 配送先 */}
      {order.shipping_address && (
        <div className="border rounded p-4 space-y-1">
          <h2 className="font-semibold">配送先</h2>
          <div>〒{order.shipping_address.postal_code}</div>
          <div>
            {order.shipping_address.prefecture}
            {order.shipping_address.city}
          </div>
          <div>
            {order.shipping_address.address_line1}
            {order.shipping_address.address_line2}
          </div>
          <div>{order.shipping_address.recipient_name}</div>
          <div>{order.shipping_address.phone}</div>
        </div>
      )}

      {/* 支払い */}
      {order.payment && (
        <div className="border rounded p-4 space-y-1">
          <h2 className="font-semibold">支払い情報</h2>
          <div>方法：{order.payment.method}</div>
          <div>状態：{order.payment.status}</div>
        </div>
      )}

      <button
        onClick={() => router.push("/mypage?page=buy")}
        className="text-blue-600 underline text-sm"
      >
        ← 購入履歴へ戻る
      </button>
    </div>
  );
}
