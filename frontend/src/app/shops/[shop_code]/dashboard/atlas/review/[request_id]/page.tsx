"use client";

import { useParams, useRouter } from "next/navigation";
import useSWR from "swr";
import { DecisionPanelReview } from "./DecisionPanelReview";

const fetcher = (url: string) => fetch(url).then((r) => r.json());

export default function AtlasReviewPage() {
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const { data, error, isLoading } = useSWR(
    `/api/shops/${shop_code}/atlas/requests/${request_id}`,
    fetcher
  );

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (error) return <div className="p-6 text-red-600">取得失敗</div>;

  return (
    <div className="p-6 space-y-6">
      <h1 className="text-2xl font-semibold">Atlas Review #{request_id}</h1>

      <DecisionPanelReview
        requestId={Number(request_id)}
        analysis={data.analysis}
      />
    </div>
  );
}
