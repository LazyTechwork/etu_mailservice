@servers(['localhost' => '127.0.0.1'])

@task('deploy')
cd /var/www/etu_mailservice
php artisan down --refresh=30
git pull origin master
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
php artisan migrate --force
php artisan up
@endtask
