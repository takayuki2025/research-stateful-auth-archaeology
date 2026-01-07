"use client";

import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import type { AxiosResponse } from "axios";
import { useAuth } from "@/ui/auth/useAuth";
import styles from "./W-StripeThankYou.module.css";

type OrderDetailResponse = {
  order_id: number;
  order_status: string;
  payment: {
    payment_id: number; // 内部ID
    provider_payment_id: string | null; // pi_...
    method: "card";
    status: string;
    method_details?: {
      receipt_number?: string;
    };
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

  useEffect(() => {
    if (!apiClient || !orderId) {
      setError("注文情報が取得できませんでした。");
      return;
    }

    apiClient
      .get(`/me/orders/${orderId}`)
      .then((res: AxiosResponse<OrderDetailResponse>) => {
        setOrder(res.data);
      })
      .catch(() => {
        setError("注文情報の取得に失敗しました。");
      });
  }, [apiClient, orderId]);

  if (error) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p>{error}</p>
          <Link href="/" className={styles.backHomeLink}>
            ホームへ戻る
          </Link>
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

        <div className={styles.actions}>
          {/* ✅ Mypage の購入履歴と完全に同じ遷移 */}
          <Link
            href={`/mypage/orders/${orderId}`}
            className={styles.backHomeLink}
          >
            注文履歴へ
          </Link>
        </div>
      </div>
    </div>
  );
}
