# /docker/php/Dockerfile (既存の内容にNode.jsのインストールを追加)
FROM php:8.4-fpm

# 🚨 Node.jsのインストールを追加 🚨
# Nuxt SSRのためにNode.jsとnpmが必要です。
COPY --from=node:20-slim /usr/local/bin/node /usr/local/bin/
COPY --from=node:20-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# 必要なシステムパッケージとPHP拡張機能をインストール (既存の内容)
RUN apt update \
  # 必要なライブラリと開発ツールをインストール
  && apt install -y git default-mysql-client zlib1g-dev libzip-dev libpng-dev libjpeg-dev libwebp-dev unzip \
  # 依存関係のないパッケージのキャッシュをクリア
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) gd pdo_mysql zip bcmath 

# Composerのインストール (既存の内容)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# ... (MailHog, wait-for-it, php.iniのコピーはそのまま)

# ワークディレクトリの設定
WORKDIR /var/www