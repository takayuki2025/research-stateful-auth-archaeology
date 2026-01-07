export type ShopRoleName = "owner" | "manager" | "staff";

export interface ShopRole {
  shop_id: number;
  shop_code: string; // "shop-a"
  role: ShopRoleName;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  email_verified: boolean;
  has_shop: boolean;
  shop_roles: ShopRole[];
  /** 旧互換（将来削除可） */
  shop_id?: number | null;

  email_verified_at: string | null;
  first_login_at: string | null;

  /** グローバルロール */
  roles: string[];

  // shop_roles: {
  //   shop_id: number;
  // }[];

  primary_shop?: {
    shop_id: number;
  } | null;
}
