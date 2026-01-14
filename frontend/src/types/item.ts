export interface CommentUser {
  id: number;
  name: string;
  user_image: string | null;
}

export interface ItemComment {
  id: number;
  comment: string;
  created_at: string;

  user: {
    id: number;
    name: string;
    user_image: string | null;
  };
}

export interface Item {
  id: number;
  shop_id?: number | null;
  user_id: number;

  name: string;
  price: number;

  brand: string | null; // 旧 fallback
  condition: string | null; // 旧 fallback
  color?: string | null;

  explain: string;
  category: string | string[];

  item_image: string | null;
  remain: number;

  // ★ これを追加
  display?: ItemDisplay;

  is_favorited?: boolean;
  favorites_count?: number;
}

export type ItemDisplay = {
  brand?: {
    name: string | null;
    source?: "ai_provisional" | "human_confirmed";
  };
  condition?: {
    name: string | null;
    source?: "ai_provisional" | "human_confirmed";
  };
  color?: {
    name: string | null;
    source?: "ai_provisional" | "human_confirmed";
  };
};
