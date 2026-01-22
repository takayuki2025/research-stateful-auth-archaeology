import type {
  AuthClient,
  LoginResult,
  RegisterResult,
} from "@/ui/auth/AuthClient";
import type { AuthUser } from "@/domain/auth/AuthUser";

type FetchMethod = "GET" | "POST" | "PATCH" | "DELETE";

// AuthClient の get/post/patch/delete は「API向け」なので /api を自動付与
function toApiUrl(url: string): string {
  if (url.startsWith("http")) return url;
  if (url.startsWith("/api/")) return url;
  if (url.startsWith("/")) return `/api${url}`;
  return `/api/${url}`;
}

async function fetchJson<T>(
  method: FetchMethod,
  url: string,
  body?: any,
  opts?: { api?: boolean },
): Promise<T> {
  const finalUrl = opts?.api ? toApiUrl(url) : url;

  const res = await fetch(finalUrl, {
    method,
    credentials: "include",
    headers: {
      Accept: "application/json",
      ...(method !== "GET" ? { "Content-Type": "application/json" } : {}),
    },
    body: method === "GET" ? undefined : JSON.stringify(body ?? {}),
  });

  if (!res.ok) {
    let msg = `Request failed: ${res.status}`;
    try {
      const ct = res.headers.get("content-type") ?? "";
      if (ct.includes("application/json")) {
        const j: any = await res.json();
        msg = j?.message ?? msg;
      } else {
        const t = await res.text();
        if (t) msg = t;
      }
    } catch {
      // ignore
    }
    const e: any = new Error(msg);
    e.status = res.status;
    throw e;
  }

  if (res.status === 204) return undefined as unknown as T;
  return (await res.json()) as T;
}

export const SanctumClient: AuthClient = {
  // =========================================================
  // AuthClient contract
  // =========================================================
  async login(email, password): Promise<LoginResult> {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });

    // Sanctum SPA：web /login を叩く構成（あなたの現在の運用に合わせる）
    await fetchJson<void>("POST", "/login", { email, password });

    const user = await this.me();
    if (!user) {
      throw new Error("Login succeeded but /api/me returned null");
    }

    return { user, isFirstLogin: false };
  },

  async register(name, email, password): Promise<RegisterResult> {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });

    // ✅ RegisterController の validate に合わせる（password_confirmation 不要）
    await fetchJson<any>("POST", "/api/register", { name, email, password });

    // Controller 側で Auth::login() 済みなので、
    // 直後に /api/me が通るはず（通らないならここで検知できる）
    const me = await this.me();
    if (!me) {
      throw new Error("Register succeeded but /api/me returned null");
    }

    // メール認証イベントは送っているので true が妥当
    return { needsEmailVerification: true };
  },

  async logout(): Promise<void> {
    await fetch("/logout", {
      method: "POST",
      credentials: "include",
    });
  },

  async me(): Promise<AuthUser | null> {
    const res = await fetch("/api/me", { credentials: "include" });
    if (!res.ok) return null;
    return (await res.json()) as AuthUser;
  },

  // =========================================================
  // AxiosLike methods (AuthClient requires these)
  // =========================================================
  async get<T = any>(url: string): Promise<T> {
    return await fetchJson<T>("GET", url, undefined, { api: true });
  },

  async post<T = any>(url: string, body?: any): Promise<T> {
    return await fetchJson<T>("POST", url, body, { api: true });
  },

  async patch<T = any>(url: string, body?: any): Promise<T> {
    return await fetchJson<T>("PATCH", url, body, { api: true });
  },

  async delete<T = any>(url: string): Promise<T> {
    return await fetchJson<T>("DELETE", url, undefined, { api: true });
  },
};
