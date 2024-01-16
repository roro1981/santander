FROM 079258014335.dkr.ecr.us-west-2.amazonaws.com/php-8.1-prod:0.0.3

ARG NR_LICENSE_KEY=' '
ARG NR_APP_NAME=''

# Copy application files
COPY ./web /var/www/html

# Install application dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# New enable newrelic
RUN sh /enable-newrelic.sh ${NR_LICENSE_KEY} ${NR_APP_NAME}
