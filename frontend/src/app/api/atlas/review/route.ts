import { NextRequest, NextResponse } from "next/server";

export const runtime = "nodejs";

type ReviewSubmit = {
  entity_type: string;
  raw_value: string;
  selected_value: string | null;
  action: "accept" | "reject" | "escalate";
  note?: string;
  analysis?: unknown;
};

export async function POST(request: NextRequest) {
  try {
    const body = (await request.json()) as ReviewSubmit;
    void body;
    return NextResponse.json({ ok: true });
  } catch (e: any) {
    return NextResponse.json(
      { message: e?.message ?? "Review submit failed" },
      { status: 500 },
    );
  }
}
