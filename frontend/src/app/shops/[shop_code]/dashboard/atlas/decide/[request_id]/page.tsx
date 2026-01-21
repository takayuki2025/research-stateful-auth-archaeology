"use client";

import { useParams, useRouter } from "next/navigation";
import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";
import { DecisionPanelReview } from "../../review/[request_id]/DecisionPanelDecide";

type ApiResponse = {
  request: {
    id: number;
    item_id: number;
    status: string;
    analysis_version: string;
  };
  analysis: {
    rule_id?: string;
    confidence?: number | null;
  } | null;
};

function normalizeApiPath(path: string): string {
  return path.startsWith("/api/") ? path.replace(/^\/api/, "") : path;
}

export default function AtlasDecidePage() {
  const router = useRouter();
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const { apiClient } = useAuth() as any;

  const unwrap = <T,>(r: any): T => {
    if (r && typeof r === "object" && "data" in r) return r.data as T;
    return r as T;
  };

  const fetcher = async (url: string): Promise<ApiResponse> => {
    const r = await apiClient.get(normalizeApiPath(url));
    return unwrap<ApiResponse>(r);
  };

  const { data, error, isLoading } = useSWR<ApiResponse>(
    `/api/shops/${shop_code}/atlas/requests/${request_id}`,
    fetcher,
  );

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (error) return <div className="p-6 text-red-600">取得失敗</div>;

  return (
    <div className="p-6 space-y-6">
      <h1 className="text-2xl font-semibold">Atlas Decide #{request_id}</h1>

      <DecisionPanelReview
        shopCode={shop_code}
        requestId={Number(request_id)}
        analysis={data?.analysis ?? null}
        onDecided={() =>
          router.push(`/shops/${shop_code}/dashboard/atlas/requests`)
        }
      />
    </div>
  );
}
