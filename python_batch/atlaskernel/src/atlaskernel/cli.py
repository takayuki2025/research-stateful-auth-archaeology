import argparse
import sys
from pathlib import Path

from atlaskernel.adapters.input_reader import read_requests
from atlaskernel.adapters.output_writer import write_result
from atlaskernel.application.analyze_entity import analyze
from atlaskernel.services.schema_validate import validate_entity_analysis_v1
from atlaskernel.services.audit_logger import AuditLogger   # ★ 追加


def main():
    parser = argparse.ArgumentParser(description="AtlasKernel CLI")
    parser.add_argument(
        "--input",
        type=str,
        help="Input JSONL file or directory. If omitted, read from stdin."
    )
    args = parser.parse_args()

    streams = []

    if args.input:
        p = Path(args.input)
        if p.is_file():
            streams.append(p.open("r", encoding="utf-8"))
        elif p.is_dir():
            for f in sorted(p.rglob("*.jsonl")):
                streams.append(f.open("r", encoding="utf-8"))
        else:
            raise ValueError(f"Invalid --input path: {args.input}")
    else:
        streams.append(sys.stdin)

    _audit = AuditLogger()   # ★ 追加（1回だけ生成）

    for stream in streams:
        for req in read_requests(stream):
            result = analyze(req)
            payload = result.model_dump(exclude_none=True)

            validate_entity_analysis_v1(payload)
            write_result(result, sys.stdout)

            _audit.log(payload)   # ★ Phase D：監査ログ

if __name__ == "__main__":
    main()
