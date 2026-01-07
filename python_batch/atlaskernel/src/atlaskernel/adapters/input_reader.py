import json
from atlaskernel.domain.request import AnalysisRequest


def read_requests(stream):
    for line in stream:
        line = line.strip()
        if not line:
            continue
        data = json.loads(line)
        yield AnalysisRequest(**data)