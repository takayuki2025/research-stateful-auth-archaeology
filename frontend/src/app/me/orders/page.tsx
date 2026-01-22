"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useAuth } from "@/ui/auth/AuthProvider";

const shipmentStatusLabel: Record<string, string> = {
  created: "発送準備中",
  packed: "梱包済み",
  shipped: "発送済み",
  in_transit: "配送中",
  delivered: "配達完了",
};

type OrderHistoryItem = {
  order_id: number;
  order_status: string;
  payment_status?: string | null;
  payment_method?: string | null;
  shipment_status?:
    | "created"
    | "packed"
    | "shipped"
    | "in_transit"
    | "delivered"
    | null;
};

export default function OrderHistoryPage() {
  const { apiClient } = useAuth() as any;
  const [orders, setOrders] = useState<OrderHistoryItem[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!apiClient) return;

    apiClient
      .get("/me/orders")
      .then((data: any) => {
        setOrders(data as OrderHistoryItem[]);
      })
      .finally(() => setLoading(false));
  }, [apiClient]);

  if (loading) return <p>注文履歴を取得中です…</p>;

  return (
    <div>
      <h1>注文履歴</h1>

      {orders.length === 0 && <p>注文はまだありません。</p>}

      <ul>
        {orders.map((o) => (
          <li key={o.order_id}>
            <Link href={`/me/orders/${o.order_id}`}>注文 #{o.order_id}</Link>

            <div>注文状態: {o.order_status}</div>

            <div>
              支払い: {o.payment_method ?? "-"} / {o.payment_status ?? "-"}
            </div>

            <div>
              配送:{" "}
              {o.shipment_status
                ? shipmentStatusLabel[o.shipment_status]
                : "未発送"}
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
