FROM php:8.4-fpm

# 必要なシステムパッケージ、PHP拡張機能、そして Supervisor をインストール
RUN apt update \
  # Supervisorを追加インストール
  && apt install -y git default-mysql-client zlib1g-dev libzip-dev libpng-dev libjpeg-dev libwebp-dev unzip supervisor \
  # 依存関係のないパッケージのキャッシュをクリア
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) gd pdo_mysql zip bcmath

# Composerのインストール
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP-FPMの設定ファイルをコピー
COPY ./conf.d/mailhog.ini /usr/local/etc/php/conf.d/mailhog.ini
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf


# ワークディレクトリの設定
WORKDIR /var/www