"use client";

import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";

import { useAuth } from "@/ui/auth/AuthProvider";
import styles from "./W-StripeThankYou.module.css";

type OrderDetailResponse = {
  order_id: number;
  order_status: string;
  payment: {
    payment_id: number;
    provider_payment_id: string | null;
    method: "card";
    status: string;
  } | null;
  shipment: {
    shipment_id: number;
    status: string;
    eta?: string | null;
  } | null;
};

export default function StripeThankYouPage() {
  const { apiClient } = useAuth();
  const searchParams = useSearchParams();
  const orderId = searchParams.get("order_id");

  const [order, setOrder] = useState<OrderDetailResponse | null>(null);
  const [error, setError] = useState<string | null>(null);

  const canFetch = !!apiClient && !!orderId;

  useEffect(() => {
    if (!canFetch) return;

    let cancelled = false;

    const fetchOrder = async () => {
      try {
        const res = await apiClient.get<OrderDetailResponse>(
          `/me/orders/${orderId}`
        );
        if (!cancelled) {
          setOrder(res);
        }
      } catch {
        if (!cancelled) {
          setError("注文情報の取得に失敗しました。");
        }
      }
    };

    fetchOrder();

    return () => {
      cancelled = true;
    };
  }, [apiClient, orderId, canFetch]);

  if (!apiClient || !orderId) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p>注文情報が取得できませんでした。</p>
          <Link href="/">ホームへ戻る</Link>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p>{error}</p>
        </div>
      </div>
    );
  }

  if (!order || !order.payment) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p>注文情報を取得中です…</p>
        </div>
      </div>
    );
  }

  return (
    <div className={styles.thankYouPage}>
      <div className={styles.messageBox}>
        <h1 className={styles.title}>ご購入ありがとうございます！</h1>
        <p>決済番号：{order.payment.provider_payment_id}</p>

        <p className={styles.message}>
          カード決済が正常に完了しました。
          <br />
          {order.shipment ? "商品発送準備中です。" : "発送情報を準備中です。"}
        </p>

        <Link
          href={`/mypage/orders/${order.order_id}`}
          className={styles.backHomeLink}
        >
          注文履歴へ
        </Link>
      </div>
    </div>
  );
}
