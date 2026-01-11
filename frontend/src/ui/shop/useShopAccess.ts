"use client";

import { useMemo } from "react";
import { useAuth } from "@/ui/auth/AuthProvider";

export type ShopRoleSlug = "owner" | "manager" | "staff";

export type ShopAccess = {
  shopCode: string;
  isAuthenticated: boolean;
  authReady: boolean;
  isLoading: boolean;

  roles: ShopRoleSlug[];
  isShopStaff: boolean;
  canAccessDashboard: boolean; // = staff 以上
  canManageShop: boolean; // = owner/manager
};

const STAFF_ROLES: ShopRoleSlug[] = ["owner", "manager", "staff"];

export function useShopAccess(shopCode: string): ShopAccess {
  const { user, isAuthenticated, authReady, isLoading } = useAuth();

  const roles = useMemo<ShopRoleSlug[]>(() => {
    if (!isAuthenticated || !user?.shop_roles) return [];
    return user.shop_roles
      .filter((r) => r.shop_code === shopCode)
      .map((r) => r.role)
      .filter((r): r is ShopRoleSlug =>
        STAFF_ROLES.includes(r as ShopRoleSlug)
      );
  }, [isAuthenticated, user, shopCode]);

  const isShopStaff = roles.length > 0;

  const canAccessDashboard = isShopStaff; // owner/manager/staff
  const canManageShop = roles.includes("owner") || roles.includes("manager");

  return {
    shopCode,
    isAuthenticated,
    authReady,
    isLoading,
    roles,
    isShopStaff,
    canAccessDashboard,
    canManageShop,
  };
}
