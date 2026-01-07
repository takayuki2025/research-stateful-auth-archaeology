from rapidfuzz import fuzz

def similarity(a: str, b: str) -> float:
    return fuzz.ratio(a, b) / 100.0