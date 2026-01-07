export const SanctumClient: AuthClient = {
  async login(email, password) {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });
    await fetch("/login", {
      method: "POST",
      credentials: "include",
      body: JSON.stringify({ email, password }),
      headers: { "Content-Type": "application/json" },
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
};
