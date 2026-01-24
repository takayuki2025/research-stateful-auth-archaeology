"use client";

import { useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { TokenStorage } from "@/infrastructure/auth/TokenStorage";

const PKCE_VERIFIER_KEY = "auth0_pkce_verifier_v1";
const OIDC_STATE_KEY = "auth0_state_v1";
const RETURN_TO_KEY = "auth0_return_to_v1";

function buildAuth0TokenUrl(domain: string) {
  const d = domain.replace(/^https?:\/\//, "").replace(/\/+$/, "");
  return `https://${d}/oauth/token`;
}

export default function AuthCallbackPage() {
  const router = useRouter();
  const sp = useSearchParams();

  useEffect(() => {
    (async () => {
      const code = sp.get("code");
      const state = sp.get("state");
      const error = sp.get("error");
      if (error) {
        TokenStorage.clear();
        router.replace(`/login?oidc_error=${encodeURIComponent(error)}`);
        return;
      }
      if (!code) {
        router.replace("/login?oidc_error=missing_code");
        return;
      }

      const domain = process.env.NEXT_PUBLIC_AUTH0_DOMAIN ?? "";
      const clientId = process.env.NEXT_PUBLIC_AUTH0_CLIENT_ID ?? "";
      const audience = process.env.NEXT_PUBLIC_AUTH0_AUDIENCE ?? "";
      const redirectUri =
        process.env.NEXT_PUBLIC_OIDC_REDIRECT_URI ??
        "http://localhost/auth/callback";

      if (!domain || !clientId || !audience) {
        router.replace("/login?oidc_error=env_missing");
        return;
      }

      const expectedState = sessionStorage.getItem(OIDC_STATE_KEY);
      sessionStorage.removeItem(OIDC_STATE_KEY);
      if (!expectedState || expectedState !== state) {
        TokenStorage.clear();
        router.replace("/login?oidc_error=state_mismatch");
        return;
      }

      const verifier = sessionStorage.getItem(PKCE_VERIFIER_KEY);
      sessionStorage.removeItem(PKCE_VERIFIER_KEY);
      if (!verifier) {
        TokenStorage.clear();
        router.replace("/login?oidc_error=missing_verifier");
        return;
      }

      const tokenUrl = buildAuth0TokenUrl(domain);

      const body = new URLSearchParams();
      body.set("grant_type", "authorization_code");
      body.set("client_id", clientId);
      body.set("code", code);
      body.set("redirect_uri", redirectUri);
      body.set("code_verifier", verifier);
      body.set("audience", audience);

      const res = await fetch(tokenUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          Accept: "application/json",
        },
        body: body.toString(),
      });

      if (!res.ok) {
        const text = await res.text().catch(() => "");
        TokenStorage.clear();
        router.replace(
          `/login?oidc_error=token_exchange_failed&status=${res.status}&detail=${encodeURIComponent(
            text.slice(0, 200),
          )}`,
        );
        return;
      }

      const json = (await res.json().catch(() => ({}))) as any;
      const accessToken =
        typeof json?.access_token === "string" ? json.access_token : "";
      if (!accessToken) {
        TokenStorage.clear();
        router.replace("/login?oidc_error=missing_access_token");
        return;
      }

      TokenStorage.save({ accessToken, refreshToken: "" });

      const returnTo = sessionStorage.getItem(RETURN_TO_KEY) ?? "/";
      sessionStorage.removeItem(RETURN_TO_KEY);
      router.replace(returnTo);
    })().catch(() => {
      TokenStorage.clear();
      router.replace("/login");
    });
  }, [router, sp]);

  return (
    <div style={{ padding: 24 }}>
      <h1>Signing in…</h1>
      <p>認証処理を完了しています。しばらくお待ちください。</p>
    </div>
  );
}
