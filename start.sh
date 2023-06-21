#! /bin/bash

docker compose up --build -d
docker compose exec php-apache composer install
docker compose exec php-apache npm install
docker compose exec php-apache npm run dev
docker compose exec php-apache php bin/console doctrine:migrations:migrate --no-interaction