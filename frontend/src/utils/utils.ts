// ======================================
// IMAGE TYPE（fallback 用）
// ======================================
export enum IMAGE_TYPE {
  USER = "user",
  ITEM = "item",
  OTHER = "other",
}

// ======================================
// getOrigin（SSR / CSR 両対応）
// ======================================
function getOrigin(): string {
  if (typeof window !== "undefined") {
    return window.location.origin;
  }
  return process.env.NEXT_PUBLIC_BACKEND_URL ?? "http://localhost";
}

// ======================================
// Fallback Images（相対 or origin）
// ======================================
function getFallback(type: IMAGE_TYPE): string {
  const origin = getOrigin();

  if (type === IMAGE_TYPE.USER) {
    return `${origin}/pictures_user/default-profile2.jpg`;
  }

  return `${origin}/storage/pictures/no-image.png`;
}

// ======================================
// getImageUrl（最終版）
// ======================================
export function getImageUrl(
  path?: string | null,
  type: IMAGE_TYPE = IMAGE_TYPE.OTHER
): string {
  const origin = getOrigin();

  if (!path) {
    return getFallback(type);
  }

  // absolute URL
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  // USER 画像は public 直下
  if (type === IMAGE_TYPE.USER) {
    return `${origin}/${path}`;
  }

  // Laravel storage absolute
  if (path.startsWith("/storage/")) {
    return `${origin}${path}`;
  }

  // storage 相対（ITEM 等）
  if (path.startsWith("item_images/") || path.startsWith("pictures/")) {
    return `${origin}/storage/${path}`;
  }

  return `${origin}/${path}`;
}

// ======================================
// onImageError
// ======================================
export const onImageError: React.ReactEventHandler<HTMLImageElement> = (e) => {
  const img = e.currentTarget;
  img.onerror = null;
  img.src = "https://placehold.co/300x300?text=No+Image";
};