import json

def write_result(result, fp):
    payload = result.model_dump(exclude_none=True)
    fp.write(json.dumps(payload, ensure_ascii=False) + "\n")