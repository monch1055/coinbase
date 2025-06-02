# Use the official PHP 8.1 image with Apache
FROM ubuntu:22.04

RUN apt-get update && apt-get install -y apache2

RUN apt-get update && apt-get install -y php8.1

RUN apt-get update && apt-get install -y mysql-server

RUN apt-get update && apt-get install unzip

# Copy PHP files to the Apache web directory
COPY app.zip /var/www/html
RUN cd /var/www/html
RUN unzip app.zip && rm -f /var/www/html/app.zip

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2ctl", "-D", "FOREGROUND"]