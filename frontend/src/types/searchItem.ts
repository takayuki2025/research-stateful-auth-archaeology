export type SearchItem = {
  id: number;
  shop_id: number;
  name: string;
  price: {
    amount: number;
    currency: string;
  };
  created_at: string;
};
