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
    // ★ 修正後
    return `${origin}/storage/pictures_user/default-profile2.jpg`;
  }

  return `${origin}/storage/pictures/no-image.png`;
}

// ======================================
// getImageUrl（最終安定版）
// ======================================
export function getImageUrl(
  path?: string | null,
  _type: IMAGE_TYPE = IMAGE_TYPE.OTHER
): string {
  const origin = getBackendOrigin();

  if (!path) {
    return getFallback(_type);
  }

  // absolute URL
  if (/^https?:\/\//i.test(path)) {
    return path;
  }

  const normalized = path.replace(/^\/+/, "");

  // ✅ すでに storage/ が含まれている場合は二重にしない
  if (normalized.startsWith("storage/")) {
    return `${origin}/${normalized}`;
  }

  // ✅ それ以外は storage 配下
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
