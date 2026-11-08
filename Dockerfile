# syntax=docker/dockerfile:1
#
# GAB Architects — TEST / ÖNİZLEME imajı (Railway). TD-060.
# Gerçek production hedefi Turhost paylaşımlı hosting'dir (DEPLOYMENT_GUIDE); bu imaj onu değiştirmez.
# Apache + .htaccess kullanılır: Turhost'a en yakın ortam (gzip, önbellek, güvenlik kuralları aynen çalışır).

# ---- 1) Ön yüz derlemesi (Vite) — Node yalnızca derleme aşamasında, çalışan imajda Node yok
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ---- 2) PHP 8.4 + Apache
FROM php:8.4-apache-bookworm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libpng-dev libjpeg62-turbo-dev libwebp-dev libavif-dev libfreetype6-dev libicu-dev libzip-dev unzip \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-avif --with-freetype \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql intl zip opcache bcmath exif \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: document root = public/, .htaccess etkin, sürüm bilgisi gizli
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
# mod_php yalnızca prefork MPM ile çalışır; Railway'de "More than one MPM loaded" (AH00534) hatası görüldü →
# event/worker kesin kapatılır (entrypoint'te de kontrol edilir)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
 && a2enmod mpm_prefork rewrite headers deflate expires \
 && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && { \
      echo '<Directory /var/www/html/public>'; \
      echo '    Options FollowSymLinks'; \
      echo '    AllowOverride All'; \
      echo '    Require all granted'; \
      echo '</Directory>'; \
      echo 'ServerName localhost'; \
      echo 'ServerTokens Prod'; \
      echo 'ServerSignature Off'; \
    } > /etc/apache2/conf-available/gab.conf \
 && a2enconf gab

# PHP: production ayarları + OPcache
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && { \
      echo 'expose_php=Off'; \
      echo 'memory_limit=256M'; \
      echo 'upload_max_filesize=20M'; \
      echo 'post_max_size=25M'; \
      echo 'max_execution_time=120'; \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
    } > "$PHP_INI_DIR/conf.d/zz-gab.ini"

WORKDIR /var/www/html

# Bağımlılıklar önce (katman önbelleği)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
 && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/gab-entrypoint
RUN chmod +x /usr/local/bin/gab-entrypoint

CMD ["gab-entrypoint"]
