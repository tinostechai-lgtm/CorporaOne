ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine AS base

# Install PHP extensions and system dependencies
RUN ( curl -sSLf https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions -o - || echo 'return 1' ) | sh -s \
    opcache intl zip bcmath soap gd mbstring redis imagick pdo_mysql pdo_pgsql

# # Frontend build stage
# # -----------------------------------------------------------
# # This stage is responsible for building the frontend assets
# FROM node:23-alpine AS frontend

# WORKDIR /var/www

# COPY package*.json /var/www/

# RUN npm install --no-audit --no-fund --prefer-offline

# COPY . /var/www

# RUN npm run build


# Build stage
# -----------------------------------------------------------
# This stage is responsible for installing Composer and
# running `composer install` to set up the PHP dependencies
FROM base AS builder

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www

# Copy the entire Laravel application code into the container
# -----------------------------------------------------------
# In Laravel, the `composer install` command often triggers
# scripts that need access to the entire application code.
# For example, the `post-autoload-dump` event might execute
# Artisan commands like `php artisan package:discover`. If the
# application code (including the `artisan` file) is not
# present, these commands will fail, leading to build errors.
# 
# By copying the entire application code before running 
# `composer install`, we ensure that all necessary files are 
# available, allowing these scripts to run successfully.
# In other cases, it would be possible to copy composer files
# first, to leverage Docker's layer caching mechanism.
# -----------------------------------------------------------
COPY . /var/www

# Copy the built assets
# COPY --from=frontend --chown=www-data:www-data /var/www/public/build /var/www/public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist

# Final stage
# -----------------------------------------------------------
# This stage is responsible for setting up the production
# environment for the PHP application.
FROM base

WORKDIR /var/www

# Install run-time dependencies
# -----------------------------------------------------------
# The php-fpm health check script requires the fcgi package
RUN apk add --no-cache fcgi

# Download and install php-fpm health check script
RUN curl -o /usr/local/bin/php-fpm-healthcheck \
    https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# Enable php fpm status page
RUN set -xe && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Use the recommended production PHP configuration
# -----------------------------------------------------------
# PHP provides development and production configurations.
# Here, we replace the default php.ini with the production
# version to apply settings optimized for performance and
# security in a live environment.
# -----------------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy the initialization script
COPY --chown=www-data:www-data ./docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p /var/www/static \
    && chown -R www-data:www-data /var/www/static

# Copy the application code and dependencies from the build stage
COPY --from=builder --chown=www-data:www-data /var/www /var/www

# Switch to the non-privileged user to run the application
USER www-data

# Change the default command to run the entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]