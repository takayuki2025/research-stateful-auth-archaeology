# --- STAGE 1: nuxt_builder (Nuxtのビルド) ---
FROM node:22-bullseye AS nuxt_builder
# ... (既存のビルド処理はそのまま。ビルド成果物を作るのが目的)
RUN npm run build

# --- STAGE 1 終了 ---

# ----------------------------------------------------

# --- STAGE 2: Nginx Production Image ---
FROM nginx:1.21.1-alpine as stage-1

# 🚨 STAGE 1からビルド成果物をコピー 🚨 (変更なし)
# COPY --from=nuxt_builder /app/frontend/.output/public /var/www/frontend/public

# 🚨 www-dataユーザー/グループのセットアップ (変更なし)
RUN deluser www-data 2>/dev/null || true && \
    delgroup www-data 2>/dev/null || true && \
    addgroup -g 82 -S www-data && \
    adduser -u 82 -D -S -G www-data www-data

# Nginx設定ファイルのコピー (変更なし)
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# SSL も同じディレクトリにある
COPY ssl /etc/nginx/ssl

# 🚨 Nginxが使用するディレクトリの所有者を www-data に変更 (変更なし)
RUN mkdir -p /var/www/backend /var/www/frontend/public && \
    chown -R www-data:www-data /var/www/backend /var/www/frontend/public && \
    chown -R www-data:www-data /var/cache/nginx /var/run /var/log/nginx

# ベースイメージが何らかのユーザーに切り替えている場合に備えて、USER rootを明示。
USER root

# コマンドはそのまま (変更なし)
ENTRYPOINT ["/usr/sbin/nginx", "-g", "daemon off;"]