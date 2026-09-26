release: php artisan migrate --force
web: { php artisan config:cache && php artisan route:cache && php artisan view:cache || php artisan optimize:clear || true; } && exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080} --no-reload
worker: php artisan queue:work --tries=3 --timeout=600
