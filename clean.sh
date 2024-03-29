#!/bin/bash

echo "Limpando cache";

php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "Executando configs";


php artisan config:cache
php artisan route:cache
php artisan optimize

echo "Finalizado";
