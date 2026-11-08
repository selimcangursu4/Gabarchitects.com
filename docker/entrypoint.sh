#!/usr/bin/env bash
# GAB Architects — test/önizleme konteyneri başlangıcı (TD-060)
set -euo pipefail
cd /var/www/html

# Kalıcı disk (Railway Volume, /data)
DATA_DIR="${GAB_DATA_DIR:-/data}"
mkdir -p "${DATA_DIR}/media" "${DATA_DIR}/private"

# APP_KEY: değişken verilmediyse kalıcı diskte bir kez üretilir ve hep aynısı kullanılır
# (anahtar sohbete/koda yazılmaz; değişirse oturumlar ve şifreli değerler geçersiz olur)
if [ -z "${APP_KEY:-}" ]; then
    if [ ! -s "${DATA_DIR}/app.key" ]; then
        php -r 'echo "base64:".base64_encode(random_bytes(32));' > "${DATA_DIR}/app.key"
        chmod 600 "${DATA_DIR}/app.key"
        echo "APP_KEY üretildi ve ${DATA_DIR}/app.key içine kaydedildi."
    fi
    APP_KEY="$(cat "${DATA_DIR}/app.key")"
    export APP_KEY
fi

# Apache: yalnızca prefork MPM (mod_php). Birden fazla MPM yüklüyse Apache açılmaz (AH00534).
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod -q mpm_prefork
echo "Etkin MPM: $(ls /etc/apache2/mods-enabled/ | grep '^mpm_' | tr '\n' ' ')"

# Railway portu
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Görsel türevleri + orijinaller kalıcı diskte (yeniden dağıtımda kaybolmasın)
rm -rf public/media storage/app/private
ln -sfn "${DATA_DIR}/media" public/media
ln -sfn "${DATA_DIR}/private" storage/app/private

php artisan migrate --force
php artisan gab:seed-if-empty
# Örnek ilanlar artık yüklenmez (kullanıcı talebi 2026-11-08: test içerikleri silindi, gerçek içerik kaldı)
php artisan gab:sync-admin
php artisan optimize
php artisan filament:optimize || true

chown -R www-data:www-data "${DATA_DIR}" storage bootstrap/cache

exec apache2-foreground
