# Myserver
Server application using Symfony 6.3.0

## Installation


* Configure a virtual host for the application, the command below with a template can be run in order to generate it:
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

* Then run the following script:

`$ ./start.sh `