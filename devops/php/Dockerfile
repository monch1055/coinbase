FROM php:8.0-cli
# Run composer update and install
RUN apt-get update && apt-get install -y

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
&& rm -f /tmp/composer-setup.*

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get update
RUN apt-get install nano  -y

WORKDIR /var/www/html
COPY boot.sh                /usr/local/bin/boot.sh
CMD ["bash", "/usr/local/bin/boot.sh"]