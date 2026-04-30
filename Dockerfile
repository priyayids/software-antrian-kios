FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libmariadb-dev \
    libicu-dev \
    zip unzip \
    git \
    smbclient \
    libsmbclient-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql intl \
    && pecl install smbclient \
    && docker-php-ext-enable smbclient

RUN a2enmod rewrite headers

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

COPY database/docker-init.sh /usr/local/bin/docker-init.sh
RUN chmod +x /usr/local/bin/docker-init.sh

COPY . /var/www/html/

RUN git config --global --add safe.directory /var/www/html \
    && composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

RUN mkdir -p /var/www/html/public/storage/uploads \
    && cp "/var/www/html/assets/img/NISCAYA LOGO.png" /var/www/html/public/storage/uploads/ \
    && cp -r /var/www/html/assets /var/www/html/public/assets \
    && chown -R www-data:www-data /var/www/html/public/storage \
    && chmod -R 775 /var/www/html/public/storage \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

CMD ["/usr/local/bin/docker-init.sh"]

EXPOSE 80
