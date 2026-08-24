#!/bin/sh

set -e

mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache 2>/dev/null || true

chmod -R ug+rwX \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache 2>/dev/null || true

exec "$@"