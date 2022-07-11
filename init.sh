#!/usr/bin/env bash
if [ -d "/var/www/html/vendor" ]
then
    composer install
else
    echo "Vendor directory already exist. No need to install"
fi

php artisan migrate:fresh

/usr/local/bin/start-container
