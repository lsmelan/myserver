#!/bin/bash

echo "Starting test containers"
COMPOSE_IGNORE_ORPHANS=True docker compose -f docker-compose.integration.yml up -d

echo "Sleep for 5 seconds"
sleep 5

echo "Configuring test database schema"
docker compose exec php-apache php bin/console --env=test doctrine:database:create
docker compose exec php-apache php bin/console --env=test -q doctrine:schema:create

echo "Running the tests"
docker compose exec php-apache php bin/phpunit tests/Functional

echo "Stopping and removing test containers"
docker compose -f docker-compose.integration.yml down --volumes
