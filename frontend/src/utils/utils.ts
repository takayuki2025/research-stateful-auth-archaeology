// ======================================
// IMAGE TYPE（fallback 用）
// ======================================
export enum IMAGE_TYPE {
  USER = "user",
  ITEM = "item",
  OTHER = "other",
}

// ======================================
// Backend Base URL
// ======================================
const BACKEND_BASE_URL =
  process.env.NEXT_PUBLIC_BACKEND_URL ?? "https://localhost";

// ======================================
// Fallback Images（実在パス）
// ======================================
const DEFAULT_USER_IMAGE = `${BACKEND_BASE_URL}/pictures_user/default-profile2.jpg`;

const DEFAULT_ITEM_IMAGE = `${BACKEND_BASE_URL}/storage/pictures/no-image.png`;

// ======================================
// getImageUrl（Laravel 実態対応版）
// ======================================
export function getImageUrl(
  path?: string | null,
  type: IMAGE_TYPE = IMAGE_TYPE.OTHER,
): string {
  if (!path) {
    return type === IMAGE_TYPE.USER ? DEFAULT_USER_IMAGE : DEFAULT_ITEM_IMAGE;
  }

  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  if (path.startsWith("/storage/")) {
    return `${BACKEND_BASE_URL}${path}`;
  }

  if (
    path.startsWith("pictures_user/") ||
    path.startsWith("item_images/") ||
    path.startsWith("pictures/")
  ) {
    return `${BACKEND_BASE_URL}/storage/${path}`;
  }

  return `${BACKEND_BASE_URL}/${path}`;
}

// ======================================
// onImageError（型安全）
// ======================================
export const onImageError: React.ReactEventHandler<HTMLImageElement> = (e) => {
  const img = e.currentTarget;
  img.onerror = null;
  img.src = "https://placehold.co/300x300?text=No+Image";
};
