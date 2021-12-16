@servers(['localhost' => 'www-data@127.0.0.1'])

@task('deploy')
cd {{ base_path() }}
php artisan down --refresh=30
git fetch --all
git reset --hard origin/master
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan up
@endtask

@finished
@telegram('5058258557:AAGhTrmOsbs8AuDA3KoWoGvsmNsbArocydA', '229341720')
@endfinished
