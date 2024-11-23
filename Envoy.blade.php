@servers(['localhost' => '127.0.0.1'])

@task('deploy', ['on' => ['localhost']])
@if ($branch)
    git fetch
    git reset --hard origin/{{ $branch }}
@endif

COMPOSER_ALLOW_SUPERUSER=1 /usr/bin/php8.2 /usr/bin/composer install -o -vv
/usr/bin/php8.2 artisan migrate --force
/usr/bin/php8.2 artisan optimize:clear
{{--/usr/bin/php8.2 artisan ziggy:generate--}}
{{--npm install--}}
{{--npm run build--}}
chown -R www-data:www-data /var/www/sitov.ro/alegeri2024
@endtask
