FROM php:7.2-apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
COPY . /var/www/html/
EXPOSE 80
WORKDIR /var/www/html
# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"