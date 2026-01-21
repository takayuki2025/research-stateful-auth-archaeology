"use client";

import { useMemo, useState } from "react";
import { useAuth } from "@/ui/auth/AuthProvider";

type Props = {
  shopCode: string;
  requestId: number;
  analysis: {
    decision?: string;
    rule_id?: string;
    confidence?: number | null;
  } | null;
  onDecided: () => Promise<void> | void;
};

export function DecisionPanelReview({
  shopCode,
  requestId,
  analysis,
  onDecided,
}: Props) {
  const { apiClient } = useAuth() as any;

  // axios-like ({data}) / fetch-like (plain) 両対応
  const unwrap = <T,>(r: any): T => {
    if (r && typeof r === "object" && "data" in r) return r.data as T;
    return r as T;
  };

  async function postDecide(body: any) {
    // "/api" は apiClient 側で付く前提なので外す
    const r = await apiClient.post(
      `/shops/${shopCode}/atlas/requests/${requestId}/decide`,
      body,
    );
    return unwrap<any>(r);
  }

  const confidence = analysis?.confidence;

  const [rejectOpen, setRejectOpen] = useState(false);
  const [reason, setReason] = useState("");
  const [saving, setSaving] = useState(false);
  const [err, setErr] = useState<string | null>(null);

  const canApprove = useMemo(() => !!analysis, [analysis]);
  const canReject = useMemo(() => !!analysis, [analysis]);

  async function approve() {
    if (!canApprove) return;
    setErr(null);
    setSaving(true);
    try {
      await postDecide({ decision: "approve" });
      await onDecided();
    } catch (e: any) {
      setErr(e?.message ?? "Approve failed");
    } finally {
      setSaving(false);
    }
  }

  async function reject() {
    if (!canReject) return;
    if (!reason.trim()) {
      setErr("Reject には理由が必須です。");
      return;
    }

    setErr(null);
    setSaving(true);
    try {
      await postDecide({
        decision: "reject",
        reason: reason.trim(),
      });
      await onDecided();
    } catch (e: any) {
      setErr(e?.message ?? "Reject failed");
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="rounded-xl border p-4 space-y-4">
      <h3 className="font-semibold text-lg">Decision</h3>

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
            {Number.isFinite(confidence)
              ? Number(confidence).toFixed(2)
              : "N/A"}
          </span>
        </div>
      </div>

      {err ? <div className="text-sm text-red-600">{err}</div> : null}

      {/* Actions */}
      <div className="flex gap-2">
        <button
          className="rounded-xl border px-4 py-2 font-semibold disabled:opacity-50"
          disabled={!canApprove || saving}
          onClick={approve}
        >
          Approve
        </button>

        <button
          className="rounded-xl border px-4 py-2 font-semibold disabled:opacity-50"
          disabled={!canReject || saving}
          onClick={() => setRejectOpen((v) => !v)}
        >
          Reject
        </button>
      </div>

      {/* Reject reason */}
      {rejectOpen ? (
        <div className="space-y-2 rounded-xl border p-3">
          <div className="text-sm font-semibold">Reject reason（必須）</div>
          <textarea
            className="w-full rounded-lg border p-2 text-sm"
            rows={3}
            value={reason}
            onChange={(e) => setReason(e.target.value)}
            placeholder="例: brand mismatch / category wrong / unsafe content ... "
          />
          <div className="flex gap-2">
            <button
              className="rounded-xl border px-4 py-2 font-semibold disabled:opacity-50"
              disabled={saving}
              onClick={reject}
            >
              Reject 確定
            </button>
            <button
              className="rounded-xl border px-4 py-2"
              disabled={saving}
              onClick={() => setRejectOpen(false)}
            >
              閉じる
            </button>
          </div>
        </div>
      ) : null}
    </div>
  );
}
