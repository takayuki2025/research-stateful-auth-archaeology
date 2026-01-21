"use client";

import { useParams } from "next/navigation";
import useSWR from "swr";
import { useAuth } from "@/ui/auth/AuthProvider";

function normalizeApiPath(path: string): string {
  return path.startsWith("/api/") ? path.replace(/^\/api/, "") : path;
}

export default function AtlasComparePage() {
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const { apiClient } = useAuth() as any;

  const unwrap = <T,>(r: any): T => {
    if (r && typeof r === "object" && "data" in r) return r.data as T;
    return r as T;
  };

  const fetcher = async (url: string) => {
    const r = await apiClient.get(normalizeApiPath(url));
    return unwrap<any>(r);
  };

  const { data, isLoading } = useSWR(
    `/api/shops/${shop_code}/atlas/requests/${request_id}/decision`,
    fetcher,
  );

  if (isLoading) return <div className="p-6">読み込み中...</div>;
  if (!data) return <div className="p-6">データなし</div>;

  return (
    <div className="p-6 space-y-4">
      <h1 className="text-2xl font-semibold">
        Atlas Before / After #{request_id}
      </h1>

      <div className="text-sm">
        Decision: <b>{data.decision_type}</b>
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div className="border rounded p-3">
          <h2 className="font-semibold mb-2">Before</h2>
          <pre className="text-xs bg-gray-50 p-2 rounded">
            {JSON.stringify(data.before, null, 2)}
          </pre>
        </div>

        <div className="border rounded p-3">
          <h2 className="font-semibold mb-2">After</h2>
          <pre className="text-xs bg-gray-50 p-2 rounded">
            {JSON.stringify(data.after, null, 2)}
          </pre>
        </div>
      </div>
    </div>
  );
}
