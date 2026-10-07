# syntax=docker/dockerfile:1

# ============================================================================
# Production image — PHP 8.2 on Apache
# ----------------------------------------------------------------------------
# Built in two stages so Composer and its cache never reach the final image.
#
#   docker build -t portfolio .
#   docker run -p 8080:80 --env-file .env portfolio
# ============================================================================

# --- Stage 1: dependencies --------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./

# --no-dev keeps development tooling out of production.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader


# --- Stage 2: runtime -------------------------------------------------------
FROM php:8.2-apache

# mysqli for the optional database; the rest are needed by the GitHub client
# and by PHPMailer.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends libzip-dev unzip; \
    docker-php-ext-install -j"$(nproc)" mysqli opcache; \
    apt-get purge -y --auto-remove libzip-dev; \
    rm -rf /var/lib/apt/lists/*

# .htaccess drives routing, caching, and the security headers, so AllowOverride
# has to permit it; without mod_rewrite the sitemap and guards do nothing.
RUN a2enmod rewrite headers expires deflate \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-available/portfolio.conf \
    && a2enconf portfolio

# Production PHP defaults, including a tuned opcache.
RUN { \
        echo 'expose_php=Off'; \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/dev/stderr'; \
        echo 'upload_max_filesize=8M'; \
        echo 'post_max_size=10M'; \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'session.cookie_httponly=1'; \
        echo 'session.cookie_samesite=Lax'; \
        echo 'session.use_strict_mode=1'; \
    } > /usr/local/etc/php/conf.d/portfolio.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

# The GitHub cache is the only path the application writes to.
RUN mkdir -p storage/cache \
    && chown -R www-data:www-data storage \
    && chmod -R 775 storage \
    && rm -f .env

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/") === false ? 1 : 0);'
