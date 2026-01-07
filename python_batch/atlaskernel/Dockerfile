FROM python:3.11-slim

WORKDIR /app

RUN apt-get update && apt-get install -y \
    build-essential \
    ca-certificates \
 && rm -rf /var/lib/apt/lists/*

RUN pip install --upgrade pip

# 依存定義
COPY pyproject.toml poetry.lock* ./

RUN pip install poetry \
 && poetry config virtualenvs.create false \
 && poetry install --no-interaction --no-root

# アプリ本体
COPY . .

# パッケージとしてインストール（atlaskernel コマンドも有効）
RUN pip install -e .

# FastAPI 起動
CMD ["uvicorn", "server:app", "--host", "0.0.0.0", "--port", "8000"]