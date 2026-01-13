"use client";

import { useParams } from "next/navigation";
import useSWR from "swr";

const fetcher = (url: string) =>
  fetch(url, { credentials: "include" }).then((r) => r.json());

export default function AtlasComparePage() {
  const { shop_code, request_id } = useParams<{
    shop_code: string;
    request_id: string;
  }>();

  const { data, isLoading } = useSWR(
    `/api/shops/${shop_code}/atlas/requests/${request_id}/decision`,
    fetcher
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
