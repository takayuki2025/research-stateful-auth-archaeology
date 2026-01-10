import { NextResponse } from "next/server";
import { atlasAnalyze, } from "../../../../lib/atlas";

export const runtime = "nodejs";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const data = await atlasAnalyze(body);
    return NextResponse.json(data);
  } catch (e: any) {
    return NextResponse.json(
      { message: e?.message ?? "Analyze failed" },
      { status: 500 }
    );
  }
}
