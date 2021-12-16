#!/bin/sh

php artisan down --refresh=30
git fetch --all
git reset --hard origin/master
composer install --no-ansi --no-dev --prefer-dist --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan up
