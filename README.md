Via Local:

1. run composer install
2. run cp .env.local .env
3. run php artisan key:generate
4. run php artisan serve

Via docker:

1. Create and start container: docker-compose up -d --build

2. Log in to container: docker exec -it --user=dev_user laravel6spa_kit_php-fpm bash

3. Run composer install: composer install

4. Copy .env.docker to .env.

5. Generate key: php artisan key:generate

6. Run migration and seed: php artisan migrate:fresh --seed

7. Access to http://localhost:8086
   Mail : admin@company.com
   Pass : 12345678

8. (Access to phpMyAdmin is http://localhost:8087)
