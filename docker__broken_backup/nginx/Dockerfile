# --- STAGE 1: nuxt_builder (Nuxtのビルドは残します) ---
FROM node:22-bullseye AS nuxt_builder
# ... (既存のビルド処理はそのまま。ビルド成果物を作るのが目的)
RUN npm run build
# ...

# ----------------------------------------------------

# --- STAGE 2: Nginx Production Image ---
FROM nginx:1.21.1 as stage-1

# Nginx設定ファイルのコピー (修正したdefault.confを使用)
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# 🚨 修正: ビルド成果物のコピーを削除またはコメントアウト 🚨
# docker-compose.ymlでボリュームマウントするため、ここではコピーしません。
# COPY --from=nuxt_builder /app/.output /var/www/frontend 

# Nginxがアクセスするためのパーミッション設定
# /var/www/frontend はボリュームマウントされるため、backendのみに限定
# RUN chown -R nginx:nginx /var/www/frontend # <-- この行を削除または修正

# 🚨 修正: マウントされるディレクトリの権限設定は必須 🚨
# backend と frontend/public の両方が www-data (PHP) と nginx でアクセス可能にする
# Nginxコンテナでは /var/www/frontend/public は空で起動し、phpコンテナからマウントされるため、
# phpコンテナから共有される /var/www/frontend/public の権限は phpコンテナ側で設定されます。
# ここでは Nginxが操作するディレクトリの権限のみを修正。
RUN mkdir -p /var/www/backend /var/www/frontend/public && \
    chown -R nginx:nginx /var/www/backend /var/www/frontend/public

# Nginxの実行ディレクトリ作成とその他の権限設定 (既存の内容)
RUN mkdir -p /var/cache/nginx/client_temp /var/run /var/log/nginx && \
    chown -R nginx:nginx /var/cache/nginx /var/run /var/log/nginx

# 実行ユーザーをnginxに変更 (既存の内容)
USER nginx