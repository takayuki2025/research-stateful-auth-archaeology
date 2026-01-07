import { AuthClient } from "../AuthClient";

const json = async (res: Response) => {
  if (!res.ok) {
    const data = await res.json().catch(() => ({}));
    throw new Error(data?.message ?? "Request failed");
  }
  return res.json();
};

export const SanctumClient: AuthClient = {
  async login(email, password) {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });

    await fetch("/login", {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ email, password }),
    });
  },

  async logout() {
    await fetch("/logout", {
      method: "POST",
      credentials: "include",
    });
  },

  async me() {
    const res = await fetch("/api/me", { credentials: "include" });
    if (!res.ok) return null;
    return res.json();
  },

  async get(url) {
    const res = await fetch(url, {
      method: "GET",
      credentials: "include",
      headers: { Accept: "application/json" },
    });
    return json(res);
  },

  async post(url, body) {
    const res = await fetch(url, {
      method: "POST",
      credentials: "include",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    return json(res);
  },

  async delete(url) {
    const res = await fetch(url, {
      method: "DELETE",
      credentials: "include",
      headers: { Accept: "application/json" },
    });
    return json(res);
  },
};
