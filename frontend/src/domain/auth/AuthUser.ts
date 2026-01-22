export type ShopRoleName = "owner" | "manager" | "staff";

export interface ShopRole {
  shop_id: number;
  shop_code: string;
  role: ShopRoleName;
}

export interface AuthUser {
  id: number;
    
  // ✅ 互換のため optional にする（Sanctum/JWT/IdaaSで揺れても壊れない）
  name: string;
  display_name?: string | null;

  email: string;

  email_verified_at: string | null;
  first_login_at: string | null;

  profile_completed: boolean;
  has_shop: boolean;

  roles: string[];
  shop_roles: ShopRole[];

  primary_shop?: {
    shop_id: number;
    name?: string;
  } | null;
}
