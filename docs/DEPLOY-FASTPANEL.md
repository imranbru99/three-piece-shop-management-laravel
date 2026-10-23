# FastPanel / nginx deployment

The current release is a development demo. These are deployment instructions to validate, not evidence of a completed production rollout. Do not use real customer/financial data until the production gaps are closed.

## Runtime

Use PHP 8.3-FPM with intl, gd, mbstring, sockets, curl, openssl, bcmath, pdo_mysql, zip and redis; MySQL 8; Redis; Node 22+ for asset builds; Supervisor and cron. Composer locks Laravel 12 and Filament 3. Keep the document root at `public/`, never the repository root.

## Release steps

1. Back up the database and media and prove a restore in staging.
2. Install the committed lockfiles: `composer install --no-dev --prefer-dist --no-interaction` and `npm ci && npm run build`.
3. Supply `.env` with a persistent APP_KEY, APP_ENV=production, APP_DEBUG=false, DEMO_MODE=false, correct APP_URL, MySQL credentials and Redis cache/session/queue settings. Never commit live secrets.
4. Run `php artisan migrate --force`, `php artisan storage:link`, and the Bengali/universal Unicode font install commands. Verify the actual font files exist; the package command may return success even after a failed download.
5. Run `php artisan config:cache`, `php artisan route:cache` and `php artisan view:cache`. Restart queue workers after switching releases.
6. Verify `/up`, public routes and production demo-denial tests. Keep package deny middleware enabled.

A production login flow is not built; disabling demo login intentionally prevents public access to the workspace. Build production auth and tenancy before a business launch.

## nginx essentials

```nginx
root /var/www/three-piece/current/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
}
location ~ /\.(?!well-known).* { deny all; }
location /iclock/ {
    client_max_body_size 10M;
    try_files $uri $uri/ /index.php?$query_string;
}
```

Attendance endpoints remain denied until device authentication, shop resolution and tenancy checks exist. The location block alone must not be treated as authorization. Custom-domain shop resolution is not implemented.

## Background services

Supervisor command: `php /var/www/three-piece/current/artisan queue:work redis --sleep=3 --tries=3 --timeout=120`, with a dedicated service user and rotated logs. Cron: `* * * * * php /var/www/three-piece/current/artisan schedule:run >> /var/log/three-piece-scheduler.log 2>&1`.

Back up MySQL and `storage/app` off-host with encryption, retention and regular restoration tests. Keep APP_KEY recoverable; changing it breaks encrypted package data. Nightly reset, Drutogoti digest, health alerts and backup scheduling have not been implemented. The Compose/VM deployment has not been tested in this restricted environment.
