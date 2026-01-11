"use client";

import { useEffect, useRef, useState } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";

import { useAuth } from "@/ui/auth/AuthProvider";
import styles from "./W-ThanksKonbini.module.css";

/* =======================
   Types
======================= */

type KonbiniStoreInfo = {
  confirmation_number?: string;
};

type KonbiniInstructions = {
  expires_at?: number;
  store?: Record<string, KonbiniStoreInfo>;
};

type Payment = {
  payment_id: number;
  method: "konbini";
  status: "requires_action" | "succeeded" | "expired";
  instructions: KonbiniInstructions | null;
};

type OrderDetailResponse = {
  order_id: number;
  order_status: string;
  payment: Payment | null;
};

export default function ThanksBuyKonbiniPage() {
  const searchParams = useSearchParams();
  const orderId = searchParams.get("order_id");
  const { apiClient } = useAuth();

  const [order, setOrder] = useState<OrderDetailResponse | null>(null);
  const [error, setError] = useState<string | null>(null);

  const retryTimerRef = useRef<NodeJS.Timeout | null>(null);
  const retryCountRef = useRef(0);

  const MAX_RETRY = 30;
  const INTERVAL_MS = 1000;

  const canFetch = !!apiClient && !!orderId;

  useEffect(() => {
    if (!canFetch) return;

    let cancelled = false;

    const fetchOrder = async () => {
      try {
        const res = await apiClient.get<OrderDetailResponse>(
          `/me/orders/${orderId}`
        );

        if (cancelled) return;

        setOrder(res);

        const payment = res.payment;

        if (
          payment &&
          payment.method === "konbini" &&
          payment.status === "requires_action" &&
          !payment.instructions &&
          retryCountRef.current < MAX_RETRY
        ) {
          retryCountRef.current += 1;
          retryTimerRef.current = setTimeout(fetchOrder, INTERVAL_MS);
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
      if (retryTimerRef.current) {
        clearTimeout(retryTimerRef.current);
      }
    };
  }, [apiClient, orderId, canFetch]);

  /* =======================
     Render Guards
  ======================= */

  if (!canFetch) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p className={styles.message}>注文情報が取得できませんでした。</p>
          <Link href="/" className={styles.backHomeLink}>
            ホームへ戻る
          </Link>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p className={styles.message}>{error}</p>
        </div>
      </div>
    );
  }

  if (!order || !order.payment) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p className={styles.message}>支払い情報を生成中です…</p>
        </div>
      </div>
    );
  }

  const payment = order.payment;

  /* =======================
     支払い完了
  ======================= */

  if (payment.status === "succeeded") {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <h1 className={styles.title}>お支払いが完了しました</h1>
          <Link href="/mypage?page=buy" className={styles.backHomeLink}>
            注文履歴へ
          </Link>
        </div>
      </div>
    );
  }

  /* =======================
     支払い待ち
  ======================= */

  if (!payment.instructions) {
    return (
      <div className={styles.thankYouPage}>
        <div className={styles.messageBox}>
          <p className={styles.message}>
            支払い情報を生成中です…
            <br />
            数秒後に自動で反映されます。
          </p>
        </div>
      </div>
    );
  }

  const instructions = payment.instructions;

  const expiresAtText = instructions.expires_at
    ? new Date(instructions.expires_at * 1000).toLocaleString("ja-JP")
    : "未設定";

  const confirmationNumber =
    instructions.store &&
    Object.values(instructions.store)[0]?.confirmation_number
      ? Object.values(instructions.store)[0]!.confirmation_number
      : "未発行（後ほど表示されます）";

  const availableStores = instructions.store
    ? Object.keys(instructions.store)
    : [];

  return (
    <div className={styles.thankYouPage}>
      <div className={styles.messageBox}>
        <h1 className={styles.title}>現在、コンビニ支払い待ちです。</h1>

        <p className={styles.message}>
          支払期限までに指定のコンビニでお支払いください。
        </p>

        <div className={styles.konbiniInfo}>
          <p>
            <strong>支払期限：</strong>
            {expiresAtText}
          </p>

          <p>
            <strong>受付番号：</strong>
            {confirmationNumber}
          </p>

          {availableStores.length > 0 && (
            <ul>
              {availableStores.map((store) => (
                <li key={store}>{store}</li>
              ))}
            </ul>
          )}
        </div>

        <Link
          href={`/mypage/orders/${orderId}`}
          className={styles.backHomeLink}
        >
          注文履歴へ
        </Link>
      </div>
    </div>
  );
}
