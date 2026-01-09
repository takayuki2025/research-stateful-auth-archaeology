class ImageContextAdapter:
    """
    Image → PolicyContext 用の最小 PoC Adapter
    将来: Vision API / OCR / CLIP / 自前CV に差し替え
    """

    def extract(self, image_path: str) -> dict:
        # PoC: 固定値（まずはここでOK）
        return {
            "brand_conflict": True,
            "detected_text": ["Apple"],
            "confidence": 0.88,
            "source": image_path,
        }