"use client";

import type { ReactNode } from "react";
import { useEffect } from "react";

export default function DashboardLayout({ children }: { children: ReactNode }) {
  useEffect(() => {
    try {
      sessionStorage.removeItem("occore_just_logged_in_v1");
      sessionStorage.removeItem("occore_owner_shop_code_v1");
      // 任意：Home側で一度だけリダイレクトするロックを追加しているならそれも消す
      sessionStorage.removeItem("occore_home_redirect_once_v1");
    } catch {
      // ignore
    }
  }, []);

  return <div className="dashboard-layout">{children}</div>;
}
