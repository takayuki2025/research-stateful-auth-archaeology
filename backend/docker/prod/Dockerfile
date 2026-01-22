FROM php:8.4-fpm

# =========================
# OS packages
# =========================
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
     nginx \
     supervisor \
     git \
     curl \
     unzip \
     zlib1g-dev \
     libzip-dev \
     libpng-dev \
     libjpeg-dev \
     libwebp-dev \
  && docker-php-ext-configure gd --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) \
     gd \
     pdo_mysql \
     zip \
     bcmath \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# =========================
# Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/backend

# repo root を build context にする前提で backend/ をコピー
COPY backend/ /var/www/backend/

# nginx / supervisor config
COPY backend/docker/prod/nginx.conf /etc/nginx/nginx.conf
COPY backend/docker/prod/site.conf /etc/nginx/conf.d/default.conf
COPY backend/docker/prod/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# =========================
# Laravel deps (prod)
# =========================
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
  && chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache \
  && chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-n"]