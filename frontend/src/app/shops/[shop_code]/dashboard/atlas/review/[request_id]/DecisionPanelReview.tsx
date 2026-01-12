"use client";

type Props = {
  requestId: number;
  analysis: {
    decision?: string;
    rule_id?: string;
    confidence?: number | null;
  };
};

export function DecisionPanelReview({ requestId, analysis }: Props) {
  const confidence = analysis?.confidence;

  return (
    <div className="rounded-xl border p-4 space-y-4">
      <h3 className="font-semibold text-lg">Decision</h3>

      {/* === Result === */}
      <div className="text-sm space-y-1">
        <div className="flex justify-between">
          <span>request_id</span>
          <span className="font-mono">{requestId}</span>
        </div>

        <div className="flex justify-between">
          <span>decision</span>
          <span className="font-mono">{analysis?.decision ?? "-"}</span>
        </div>

        <div className="flex justify-between">
          <span>rule</span>
          <span className="font-mono">{analysis?.rule_id ?? "-"}</span>
        </div>

        <div className="flex justify-between">
          <span>confidence</span>
          <span className="font-mono">
            {Number.isFinite(confidence) ? confidence!.toFixed(2) : "N/A"}
          </span>
        </div>
      </div>

      {/* === Actions === */}
      <div className="flex gap-4 pt-2">
        <button
          type="button"
          className="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700"
          onClick={() => {
            console.log("APPROVE", requestId);
          }}
        >
          Approve
        </button>

        <button
          type="button"
          className="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
          onClick={() => {
            console.log("REJECT", requestId);
          }}
        >
          Reject
        </button>
      </div>
    </div>
  );
}
