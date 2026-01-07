# --- 開発/実行ステージ (builderという名前は開発サーバーのために保持) ---
FROM node:22-bullseye AS builder
WORKDIR /app

# 依存に必要なビルドツールを確実に使えるように修正
# 必要なビルドツール (build-essential, python3) はインストール済みですが、
# コンパイルがより安定するように、これらのツールを先にインストールします。
RUN apt-get update && apt-get install -y build-essential python3 && rm -rf /var/lib/apt/lists/*

# パッケージをコピー
COPY frontend/package*.json ./

# 依存関係のインストールステップを修正:
# 1. 'npm cache clean --force' を追加して、古いキャッシュが原因のバインディングエラーを防ぐ。
# 2. 'npm install' を実行する前に、確実にネイティブビルドツールが利用可能であることを確認。
RUN npm cache clean --force && \
    rm -f package-lock.json && \
    npm install --legacy-peer-deps

# ソースコードはボリュームマウントされるため、ここではコピーしません。

# 開発用の実行環境として使用
# command: npm run dev は docker-compose.yml で指定済み