# Myserver
Server application using Symfony 6.3.0, postgres and Redis.

## Installation

Configure a virtual host for the application:
```
echo "<VirtualHost *:80>
    ServerName ${SERVER_NAME:-localhost}
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>" > ./ci/build/apache/myapp.conf
```

Now to install and start all dependencies, run:

`$ ./start.sh `

At this point you should have your project installed and running. Remember to run the script whenever you make or pull changes from the repository that affects any dependencies.

## Integration tests

They are meant to test admin and api routes, the script bellow will create temporary docker containers for Redis and Postgres during the tests, but as soon the tests are completed they will be stopped and removed:

`$ ./run_integration_test.sh `

## PHP CS Fixer

If you want to make sure your new code follows standards then it's recommended that the following script is run before you commit the changes to git. The idea is that this tool can be integrated for CI/CD, ideally with unit tests/functional tests and PHPStan as well.

`docker compose exec php-apache vendor/bin/php-cs-fixer fix`