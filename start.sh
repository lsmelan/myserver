#! /bin/bash

echo "<VirtualHost *:80>
    ServerName ${SERVER_NAME:-localhost}
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>" > ./ci/build/apache/myapp.conf

docker compose up --build -d
docker compose exec php-apache composer install