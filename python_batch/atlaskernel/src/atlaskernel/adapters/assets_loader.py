from importlib import resources

def load_assets(ref: str) -> list[str]:
    """
    Backward compatible loader:
    - first tries {ref}.txt
    - then tries {ref}.tsv
    """
    base = resources.files("atlaskernel.assets")

    for ext in ("txt", "tsv"):
        try:
            with base.joinpath(f"{ref}.{ext}").open(encoding="utf-8") as f:
                return [
                    line.strip()
                    for line in f
                    if line.strip() and not line.strip().startswith("#")
                ]
        except FileNotFoundError:
            continue

    return []