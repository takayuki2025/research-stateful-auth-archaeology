"use client";

import React, { useMemo, useState } from "react";
import { useParams, useRouter } from "next/navigation";

import { useAuth } from "@/ui/auth/useAuth";
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

const stripePromise = loadStripe(
  process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY!,
);

type PaymentMethod = "" | "card" | "konbini";

/* ================= Wrapper ================= */
export default function PurchaseConfirmPageWrapper() {
  return (
    <Elements stripe={stripePromise}>
      <PurchaseConfirmPage />
    </Elements>
  );
}

/* ================= Page ================= */
function PurchaseConfirmPage() {
  const router = useRouter();
  const params = useParams();
  const { apiClient, isAuthenticated, isLoading: isAuthLoading } = useAuth();

  const stripe = useStripe();
  const elements = useElements();

  const itemId = useMemo(() => {
    const raw = (params as any).items_id;
    return Number(raw);
  }, [params]);

  const { item, isLoading: isItemLoading } = useItemDetailSWR(itemId);
  const { address, isLoading: isAddressLoading } = useUserPrimaryAddressSWR();

  const [payment, setPayment] = useState<PaymentMethod>("");

  /* ================= Early return ================= */
  if (isAuthLoading || isItemLoading || isAddressLoading) {
    return <div className={styles.loadingOverlay}>購入情報を読み込み中...</div>;
  }

  if (!item) {
    return <div className={styles.loadingOverlay}>商品が見つかりません</div>;
  }

  /* ================= canPurchase ================= */
  const canPurchase =
    isAuthenticated && item.remain > 0 && payment !== "" && !!address?.id;

  /* ================= submit ================= */
  const submitPurchase = async () => {
    if (!canPurchase || !apiClient || !address) return;

    try {
      // ① Order 作成
      const orderRes = await apiClient.post("/orders", {
        shop_id: item.shop_id,
        items: [
          {
            item_id: item.id,
            name: item.name,
            price_amount: item.price,
            price_currency: "JPY",
            quantity: 1,
            image_path: item.item_image,
          },
        ],
      });

      const orderId = orderRes.data.order_id;

      // ② 配送先確定
      await apiClient.post(`/orders/${orderId}/address`, {
        address_id: address.id,
      });

      await apiClient.post(`/orders/${orderId}/confirm`);

      // ④ 決済開始
      const paymentRes = await apiClient.post("/payments/start", {
        order_id: orderId,
        method: payment,
      });

      if (payment === "card") {
        if (!stripe || !elements) return;

        const card = elements.getElement(CardElement);
        if (!card) return;

        const { client_secret } = paymentRes.data;

        const result = await stripe.confirmCardPayment(client_secret, {
          payment_method: { card },
        });

        if (result.error) {
          alert(result.error.message);
          return;
        }

        router.push(`/thanks/buy/stripe-card?order_id=${orderId}`);
      } else {
        router.push(`/thanks/buy/konbini?order_id=${orderId}`);
      }
    } catch (e: any) {
      console.error(e);
      alert(
        e?.response?.data?.message ?? e?.message ?? "購入処理に失敗しました",
      );
    }
  };

  /* ================= JSX ================= */
  return (
    <div className={styles.item_buy_wrapper}>
      <div className={styles.item_buy_contents}>
        <div className={styles.item_buy_lr}>
          {/* LEFT */}
          <div className={styles.item_buy_l}>
            {/* 商品 */}
            <div className={styles.item_buy_content_section}>
              <div className={styles.item_buy_image}>
                <img src={getImageUrl(item.item_image)} alt={item.name} />
              </div>
              <div>
                <h3 className={styles.item_name}>{item.name}</h3>
                <p className={styles.item_price}>
                  ¥{item.price.toLocaleString()}
                </p>
              </div>
            </div>

            {/* 支払い方法 */}
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

            {/* Card */}
            {payment === "card" && stripe && elements && (
              <div className={styles.item_buy_content_section}>
                <h4>カード情報</h4>

                <div className={styles.stripeCardWrapper}>
                  <div
                    style={{
                      padding: "12px",
                      border: "1px solid #d1d5db",
                      borderRadius: "6px",
                      backgroundColor: "#ffffff",
                    }}
                  >
                    <CardElement
                      options={{
                        hidePostalCode: true,
                        style: {
                          base: {
                            fontSize: "16px",
                            color: "#111827",
                            lineHeight: "24px",
                            "::placeholder": {
                              color: "#9ca3af",
                            },
                          },
                          invalid: {
                            color: "#dc2626",
                          },
                        },
                      }}
                    />
                  </div>
                </div>
              </div>
            )}

            {/* 配送先 */}
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
              <p>商品代金: ¥{item.price.toLocaleString()}</p>
              <p>支払い方法: {payment || "未選択"}</p>

              <button disabled={!canPurchase} onClick={submitPurchase}>
                購入する
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
