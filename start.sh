#! /bin/bash

docker compose up --build -d
docker compose exec php-apache composer install