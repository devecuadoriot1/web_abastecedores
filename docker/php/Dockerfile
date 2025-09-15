FROM php:8.2-fpm-alpine

ARG UID=1000
ARG GID=1000
ARG TZ=America/Guayaquil

# --- Paquetes base + toolchain para compilar extensiones ---
# Nota: $PHPIZE_DEPS incluye autoconf, make, etc.
RUN apk add --no-cache \
    bash tzdata git acl shadow \
    $PHPIZE_DEPS \
    icu-dev libzip-dev oniguruma-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libxml2-dev curl-dev

# --- Extensiones PHP necesarias para Laravel ---
# (compilar SIEMPRE como root, antes de cambiar USER)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
    intl gd zip bcmath xml curl opcache pdo_mysql

# --- Zona horaria ---
ENV TZ=${TZ}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
 && echo $TZ > /etc/timezone \
 && printf "date.timezone=%s\n" "$TZ" > /usr/local/etc/php/conf.d/zz-timezone.ini

# --- Composer dentro del contenedor ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_CACHE_DIR=/tmp/composer

# --- Ajustes PHP opcionales ---
COPY docker/php/zz-custom.ini /usr/local/etc/php/conf.d/zz-custom.ini

# --- Mapear UID/GID para evitar problemas de permisos con volúmenes en Windows ---
# (Se ejecuta como root; 'shadow' ya instalado para usermod/groupmod)
RUN groupmod -g ${GID} www-data \
 && usermod  -u ${UID} -g ${GID} www-data || true

WORKDIR /var/www

# Cambiar a www-data SOLO después de compilar extensiones
USER www-data
