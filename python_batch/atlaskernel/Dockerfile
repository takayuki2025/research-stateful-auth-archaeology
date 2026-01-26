FROM python:3.14-slim

WORKDIR /app
ENV PYTHONPATH=/app/src

RUN apt-get update && apt-get install -y \
    build-essential \
    ca-certificates \
    poppler-utils \
    tesseract-ocr \
    tesseract-ocr-jpn \
    tesseract-ocr-jpn-vert \
 && rm -rf /var/lib/apt/lists/*

# ✅ Debian系のtessdataパス（あなたのログのパスと一致）
ENV TESSDATA_PREFIX=/usr/share/tesseract-ocr/5/tessdata

RUN pip install --upgrade pip

COPY pyproject.toml ./

RUN pip install poetry \
 && poetry config virtualenvs.create false \
 && poetry install --no-interaction --no-root --no-ansi

COPY . .

RUN pip install -e .
