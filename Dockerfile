FROM php:7.4-apache

# Add and enable php extensions
RUN apt update && apt install -y git

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

# Enable apache rewrite engine
RUN a2enmod rewrite

# Workdir
WORKDIR /var/www

# Composer install
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer
ADD composer.json composer.json
ADD composer.lock composer.lock
RUN composer install

# Copy directories
COPY . /var/www/

RUN rm -rf /var/www/html
RUN ln -s public html

RUN chown -R www-data:www-data /var/www
