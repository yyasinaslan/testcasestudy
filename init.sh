#!/usr/bin/env bash

result=$(composer install --dry-run 2>&1 | grep -c "Nothing to install")

if [[ $result > 0 ]]; then
    echo "Packages already installed!"
else
    composer install
fi

php artisan migrate:fresh

/usr/local/bin/start-container
