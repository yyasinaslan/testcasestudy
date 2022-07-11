#!/bin/bash

# Gerekli paketleri yukluyoruz
composer install

# Veritabani tablolarini olusturuyoruz
php artisan migrate:fresh
