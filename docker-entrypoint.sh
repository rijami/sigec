#!/bin/bash
set -e

# ─── Garantizar permisos correctos en directorios de escritura ───────────────
mkdir -p \
    /var/www/data/cache \
    /var/www/data/logs \
    /var/www/data/sessions \
    /var/log/sigec

chown -R www-data:www-data \
    /var/www/data/ \
    /var/log/sigec

chmod -R 775 \
    /var/www/data/ \
    /var/log/sigec

echo "[SIGEC] Entrypoint listo. Iniciando Apache..."
exec "$@"
