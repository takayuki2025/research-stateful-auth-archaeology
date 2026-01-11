"use client";

import React, { useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";

import { useAuth } from "@/ui/auth/AuthProvider";
import { useItemDetailSWR } from "@/services/useItemDetailSWR";
import { useUserPrimaryAddressSWR } from "@/services/useUserPrimaryAddressSWR";
import { getImageUrl } from "@/utils/utils";
import styles from "./W-Purchase-Confirm.module.css";

import { loadStripe } from "@stripe/stripe-js";
import {
  Elements,
  CardElement,
  useStripe,
  useElements,
} from "@stripe/react-stripe-js";

/* ================= Stripe ================= */
const stripePromise = loadStripe(
  process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY!,
  { locale: "ja" }
);

type PaymentMethod = "" | "card" | "konbini";

/* ================= Wrapper ================= */
export default function PurchaseConfirmPageWrapper() {
  return (
    <Elements
      stripe={stripePromise}
      options={{
        locale: "ja",
        appearance: { theme: "stripe" },
      }}
    >
      <PurchaseConfirmPage />
    </Elements>
  );
}

type CreateOrderResponse = {
  order_id: number;
};

type StartPaymentResponse = {
  client_secret: string;
};

/* ================= Page ================= */
function PurchaseConfirmPage() {
  const router = useRouter();
  const params = useParams();
  const { apiClient, isAuthenticated, isLoading: isAuthLoading } = useAuth();

  const stripe = useStripe();
  const elements = useElements();

  const itemId = useMemo(() => {
    const raw = (params as any).items_id;
    const n = Number(raw);
    return Number.isNaN(n) ? null : n;
  }, [params]);

  const {
    item,
    isLoading: isItemLoading,
    isError: isItemError,
  } = useItemDetailSWR(itemId);

  const {
    address,
    isLoading: isAddressLoading,
    isError: isAddressError,
  } = useUserPrimaryAddressSWR();

  const [payment, setPayment] = useState<PaymentMethod>("");
  const [processing, setProcessing] = useState(false);

  /* ================= Guard ================= */
  if (isAuthLoading || isItemLoading || isAddressLoading) {
    return <div className={styles.loadingOverlay}>購入情報を読み込み中...</div>;
  }

  if (isItemError || isAddressError) {
    return (
      <div className={styles.loadingOverlay}>
        情報の取得に失敗しました。時間をおいて再度お試しください。
      </div>
    );
  }

  if (!item) {
    return (
      <div className={styles.loadingOverlay}>購入情報を準備しています...</div>
    );
  }

  const resolvedItem = item;

  /* ================= canPurchase ================= */
  const canPurchase =
    isAuthenticated &&
    resolvedItem.remain > 0 &&
    payment !== "" &&
    !!address?.id &&
    !processing;

  /* ================= submit ================= */
  const submitPurchase = async () => {
    if (!canPurchase || !apiClient || !address) return;

    try {
      setProcessing(true);

      // ① Order 作成
      const orderRes = await apiClient.post<CreateOrderResponse>("/orders", {
        shop_id: resolvedItem.shop_id,
        items: [
          {
            item_id: resolvedItem.id,
            name: resolvedItem.name,
            price_amount: resolvedItem.price,
            price_currency: "JPY",
            quantity: 1,
            image_path: resolvedItem.item_image,
          },
        ],
      });

      const orderId = orderRes.order_id;

      // ② 配送先確定
      await apiClient.post(`/orders/${orderId}/address`, {
        address_id: address.id,
      });

      // ③ Order 確定
      await apiClient.post(`/orders/${orderId}/confirm`);

      // ④ Payment 開始
      const paymentRes = await apiClient.post<StartPaymentResponse>(
        "/payments/start",
        {
          order_id: orderId,
          method: payment,
        }
      );

      if (payment === "card") {
        if (!stripe || !elements) {
          alert("決済の準備が整っていません。");
          setProcessing(false);
          return;
        }

        const card = elements.getElement(CardElement);
        if (!card) {
          setProcessing(false);
          return;
        }

        const result = await stripe.confirmCardPayment(
          paymentRes.client_secret,
          {
            payment_method: { card },
          }
        );

        if (result.error) {
          alert(result.error.message);
          setProcessing(false);
          return;
        }

        router.replace(`/thanks/buy/stripe-card?order_id=${orderId}`);
      } else {
        router.replace(`/thanks/buy/konbini?order_id=${orderId}`);
      }
    } catch (e: any) {
      console.error(e);
      alert(
        e?.response?.data?.message ?? e?.message ?? "購入処理に失敗しました"
      );
      setProcessing(false);
    }
  };

  /* ================= JSX（完全固定） ================= */
  return (
    <div className={styles.item_buy_wrapper}>
      <div className={styles.item_buy_contents}>
        <div className={styles.item_buy_lr}>
          {/* LEFT */}
          <div className={styles.item_buy_l}>
            <div className={styles.item_buy_content_section}>
              <div className={styles.item_buy_image}>
                <img
                  src={getImageUrl(resolvedItem.item_image)}
                  alt={resolvedItem.name}
                />
              </div>
              <div>
                <h3 className={styles.item_name}>{resolvedItem.name}</h3>
                <p className={styles.item_price}>
                  ¥{resolvedItem.price.toLocaleString()}
                </p>
              </div>
            </div>

            <div className={styles.item_buy_content_section}>
              <h4>支払い方法</h4>
              <select
                value={payment}
                onChange={(e) => setPayment(e.target.value as PaymentMethod)}
              >
                <option value="">選択してください</option>
                <option value="konbini">コンビニ支払い</option>
                <option value="card">クレジットカード支払い</option>
              </select>
            </div>

            {payment === "card" && (
              <div className={styles.item_buy_content_section}>
                <h4>カード情報</h4>
                <div className={styles.stripeCardWrapper}>
                  <CardElement
                    options={{
                      hidePostalCode: true,
                      style: {
                        base: {
                          fontSize: "16px",
                        },
                      },
                    }}
                  />
                </div>
              </div>
            )}

            <div className={styles.item_buy_content_section}>
              <h4>配送先</h4>
              {address ? (
                <div>
                  <p>〒{address.postNumber}</p>
                  <p>
                    {address.prefecture} {address.city}
                  </p>
                  <p>{address.addressLine1}</p>
                </div>
              ) : (
                <p className={styles.warnText}>配送先住所が未登録です</p>
              )}
            </div>
          </div>

          {/* RIGHT */}
          <div className={styles.item_buy_r}>
            <div className={styles.item_buy_summary_box}>
              <p>商品代金: ¥{resolvedItem.price.toLocaleString()}</p>
              <p>支払い方法: {payment || "未選択"}</p>
              <button disabled={!canPurchase} onClick={submitPurchase}>
                {processing ? "処理中..." : "購入する"}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
