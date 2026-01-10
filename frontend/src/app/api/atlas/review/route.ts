import { NextResponse } from "next/server";

export const runtime = "nodejs";

type ReviewSubmit = {
  entity_type: string;
  raw_value: string;
  selected_value: string | null;
  action: "accept" | "reject" | "escalate";
  note?: string;
  analysis?: any; // 元の AnalyzeResponse を添付しても良い
};

export async function POST(request: Request) {
  try {
    const body = (await request.json()) as ReviewSubmit;

    // 最小実装：サーバーログへ
    console.log("[REVIEW_SUBMIT]", {
      ts: new Date().toISOString(),
      ...body,
    });

    return NextResponse.json({ ok: true });
  } catch (e: any) {
    return NextResponse.json(
      { message: e?.message ?? "Review submit failed" },
      { status: 500 }
    );
  }
}
