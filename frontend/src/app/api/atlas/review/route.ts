import { NextRequest, NextResponse } from "next/server";

export const runtime = "nodejs";

type ReviewSubmit = {
  entity_type: string;
  raw_value: string;
  selected_value: string | null;
  action: "accept" | "reject" | "escalate";
  note?: string;
  analysis?: unknown; // 元の AnalyzeResponse を添付しても良い
};

export async function POST(request: NextRequest) {
  try {
    const body = (await request.json()) as ReviewSubmit;

    // 本番固定：ここでは副作用を持たない（必要なら backend に転送する設計へ）
    // ※ 開発中にログが必要なら、環境変数でガードして一時的に出すのが安全

    return NextResponse.json({ ok: true });
  } catch (e: any) {
    return NextResponse.json(
      { message: e?.message ?? "Review submit failed" },
      { status: 500 },
    );
  }
}
