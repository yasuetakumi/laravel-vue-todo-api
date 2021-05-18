# [NOTE] Please prepare .env file before the follows.
# In docker container, "chmod +x init_setup.sh && ./init_setup.sh"
cp .env.docker .env
mkdir -p storage/framework/cache/data && \
mkdir -p storage/app/uploads && \
mkdir -p storage/framework/sessions && \
mkdir -p storage/framework/views && \
mkdir -p storage/framework/cache

composer install

php artisan key:generate && \
php artisan storage:link && \
php artisan config:cache && \
npm install && \
npm run dev

# php artisan migrate
# php artisan migrate:fresh --seed
