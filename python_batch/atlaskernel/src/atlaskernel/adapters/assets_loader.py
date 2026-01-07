from importlib import resources

def load_assets(ref: str) -> list[str]:
    try:
        with resources.files("atlaskernel.assets").joinpath(f"{ref}.txt").open() as f:
            return [line.strip() for line in f if line.strip()]
    except FileNotFoundError:
        return []