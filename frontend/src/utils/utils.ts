// ======================================
// IMAGE TYPE
// ======================================
export enum IMAGE_TYPE {
  USER = "user",
  ITEM = "item",
  OTHER = "other",
}

// ======================================
// Backend Origin（環境依存を排除）
// ======================================
function getBackendOrigin(): string {
  if (typeof window !== "undefined") {
    return window.location.origin;
  }
  return "";
}

// ======================================
// Fallback Images
// ======================================
function getFallback(type: IMAGE_TYPE): string {
  const origin = getBackendOrigin();

  if (type === IMAGE_TYPE.USER) {
    return `${origin}/storage/pictures_user/default-profile2.jpg`;
  }

  return `${origin}/storage/pictures/no-image.png`;
}

// ======================================
// getImageUrl（最終安定版）
// ======================================
export function getImageUrl(
  path?: string | null,
  type: IMAGE_TYPE = IMAGE_TYPE.OTHER
): string {
  const origin = getBackendOrigin();

  if (!path) {
    return getFallback(type);
  }

  // absolute URL
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  const normalized = path.replace(/^\/+/, "");

  if (normalized.startsWith("storage/")) {
    return `${origin}/${normalized}`;
  }

  return `${origin}/storage/${normalized}`;
}

// ======================================
// onImageError
// ======================================
export const onImageError: React.ReactEventHandler<HTMLImageElement> = (e) => {
  const img = e.currentTarget;
  img.onerror = null;
  img.src = "https://placehold.co/300x300?text=No+Image";
};
