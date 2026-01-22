import { NextRequest, NextResponse } from "next/server";

type Params = { id: string };

export async function POST(req: NextRequest, ctx: { params: Promise<Params> }) {
  const { id } = await ctx.params;

  const useMock = process.env.ATLAS_USE_MOCK === "1";
  if (useMock) {
    const body = await req.json().catch(() => ({}));
    return NextResponse.json({
      ok: true,
      item: {
        id,
        decision:
          (body as any).action === "reject" ? "rejected" : "auto_accept",
        canonical_value: (body as any).canonical_value ?? null,
      },
    });
  }

  const base = process.env.ATLAS_INTERNAL_URL;
  if (!base)
    return NextResponse.json(
      { error: "ATLAS_INTERNAL_URL is not set" },
      { status: 500 },
    );

  const body = await req.text();
  const upstream = new URL(
    `/v1/reviews/${encodeURIComponent(id)}/resolve`,
    base,
  );

  const res = await fetch(upstream.toString(), {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body,
  });

  const text = await res.text();
  return new NextResponse(text, {
    status: res.status,
    headers: {
      "Content-Type": res.headers.get("content-type") ?? "application/json",
    },
  });
}
