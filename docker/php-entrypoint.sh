#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    git config --global --add safe.directory /var/www/html || true
    composer install
fi

mkdir -p writable/cache writable/debugbar writable/logs writable/session writable/uploads
chmod -R ug+rwX writable

exec "$@"
