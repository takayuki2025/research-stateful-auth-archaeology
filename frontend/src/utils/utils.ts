// ======================================
// IMAGE TYPE
// ======================================
export enum IMAGE_TYPE {
  USER = "user",
  ITEM = "item",
  OTHER = "other",
}

// ======================================
// Backend Origin（単一責任）
// ======================================
function getBackendOrigin(): string {
  return (
    process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/$/, "") ||
    "http://localhost"
  );
}

// ======================================
// Fallback Images
// ======================================
function getFallback(type: IMAGE_TYPE): string {
  const origin = getBackendOrigin();

  if (type === IMAGE_TYPE.USER) {
    return `${origin}/pictures_user/default-profile2.jpg`;
  }

  return `${origin}/storage/pictures/no-image.png`;
}

// ======================================
// getImageUrl（安定版）
// ======================================
export function getImageUrl(
  path?: string | null,
  type: IMAGE_TYPE = IMAGE_TYPE.OTHER
): string {
  const origin = getBackendOrigin();

  if (!path) {
    return getFallback(type);
  }

  // absolute URL（S3 等）
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  // USER 画像（public 直下）
  if (type === IMAGE_TYPE.USER) {
    return `${origin}/${path.replace(/^\/+/, "")}`;
  }

  // Laravel storage absolute
  if (path.startsWith("/storage/")) {
    return `${origin}${path}`;
  }

  // storage 相対（ITEM 等）
  if (path.startsWith("item_images/") || path.startsWith("pictures/")) {
    return `${origin}/storage/${path}`;
  }

  // その他（念のため）
  return `${origin}/${path.replace(/^\/+/, "")}`;
}

// ======================================
// onImageError
// ======================================
export const onImageError: React.ReactEventHandler<HTMLImageElement> = (e) => {
  const img = e.currentTarget;
  img.onerror = null;
  img.src = "https://placehold.co/300x300?text=No+Image";
};
