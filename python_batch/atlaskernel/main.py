from atlaskernel.application.analyze_entity import analyze
from atlaskernel.adapters.mysql_reader import read_requests_from_db
from atlaskernel.adapters.mysql_writer import write_results_to_db
from atlaskernel.api.routes.reviews import router as review_router
from atlaskernel.api.routes.extract import router as extract_router

app.include_router(review_router)
app.include_router(extract_router)

def main():
    requests = read_requests_from_db()
    pairs = []

    for item_id, request in requests:   # ★ ここが重要
        results = analyze(request)

        for result in results:
            pairs.append((item_id, result))

    write_results_to_db(pairs)
    print("[OK] AtlasKernel DB pipeline executed")


if __name__ == "__main__":
    main()