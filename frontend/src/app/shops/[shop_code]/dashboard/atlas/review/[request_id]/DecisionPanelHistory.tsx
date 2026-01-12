"use client";

type Props = {
  decisions: {
    id: number;
    decision_type: string;
    decision_reason?: string | null;
    note?: string | null;
    decided_at: string;
  }[];
};

export function DecisionPanelHistory({ decisions }: Props) {
  if (decisions.length === 0) {
    return <div className="text-gray-500">判断履歴はありません。</div>;
  }

  return (
    <div className="space-y-3">
      {decisions.map((d) => (
        <div key={d.id} className="border rounded p-3 space-y-1">
          <div className="font-semibold">
            {d.decision_type === "approve" ? "Approved" : "Rejected"}
          </div>
          <div className="text-sm text-gray-600">{d.decided_at}</div>
          {d.decision_reason && (
            <div className="text-sm">理由: {d.decision_reason}</div>
          )}
          {d.note && <div className="text-sm">{d.note}</div>}
        </div>
      ))}
    </div>
  );
}
