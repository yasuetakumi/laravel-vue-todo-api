# [NOTE] Please prepare .env file before the follows.
mkdir -p storage/framework/cache/data
mkdir -p storage/app/uploads
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
composer install
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan migrate
npm install
npm run dev
