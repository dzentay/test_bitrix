FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install --no-audit --no-fund

COPY webpack.config.js ./
COPY frontend ./frontend

RUN npm run build

FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip zip git libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
COPY local ./local
COPY bin ./bin
COPY public ./public
COPY docker ./docker

COPY --from=frontend /app/public/assets ./public/assets

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 8080

CMD ["sh", "docker/entrypoint.sh"]