#!/bin/ash

cp .env.example .env

composer install

php artisan key:generate
php artisan storage:link

php artisan migrate:fresh --seed
