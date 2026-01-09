class ImageContextAdapter:
    def extract(self, image_path: str) -> dict:
        # 最小PoC：いまはダミー。次でOCR/CLIP/ロゴ検出へ差し替え。
        return {
            "brand_conflict": True,
            "detected_text": ["Apple"],
            "confidence": 0.88,
            "image_path": image_path,
        }