#!/usr/bin/env bash
# Wait for MySQL to start

host=${DB_HOST}
port=3306

echo -n "waiting for TCP connection to database:..."

while ! nc -z -w 1 $host $port 2>/dev/null
do
  echo -n "."
  sleep 1
done

echo 'ok'

# Prepare application
COMPOSER_MEMORY_LIMIT=2G composer install

php-fpm --allow-to-run-as-root
