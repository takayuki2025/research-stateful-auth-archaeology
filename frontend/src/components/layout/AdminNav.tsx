"use client";
import Link from "next/link";

export function AdminNav() {
  return (
    <nav style={{ marginBottom: 24 }}>
      <Link href="/" style={{ marginRight: 12 }}>
        🏠 Home
      </Link>
      <Link href="/admin/entity-dashboard" style={{ marginRight: 12 }}>
        📊 Dashboard
      </Link>
      <Link href="/admin/entity-reviews">🧠 Reviews</Link>
    </nav>
  );
}
