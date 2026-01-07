export type DisplayType = "STAR" | "COMET" | null;

export interface PublicItem {
  id: number;
  name: string;
  price: number;
  itemImagePath: string | null;
  brandPrimary: string | null;
  conditionName: string | null;
  colorName: string | null;
  publishedAt: string | null;

  displayType: DisplayType;

  // ❤️ 追加（これが本体）
  isFavorited: boolean;
}
