FROM python:3.11-slim

WORKDIR /app
ENV PYTHONPATH=/app/src

RUN apt-get update && apt-get install -y \
    build-essential \
    ca-certificates \
 && rm -rf /var/lib/apt/lists/*

RUN pip install --upgrade pip

# ★ poetry.lock を使わない
COPY pyproject.toml ./

RUN pip install poetry \
 && poetry config virtualenvs.create false \
 && poetry install --no-interaction --no-root --no-ansi

COPY . .

RUN pip install -e .