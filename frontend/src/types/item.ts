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

  brand: string | null;
  explain: string;

  condition: string | null;

  // JSON 文字列 or JSON 配列の両方に対応
  category: string | string[];

  item_image: string | null;

  remain: number;

  // お気に入り
  is_favorited?: boolean;
  favorites_count?: number;
}

