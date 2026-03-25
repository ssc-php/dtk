# syntax=docker/dockerfile:1

###
# PHP Dev Container
# Utility Tools: PHP, bash, Composer, Box, micro.sfx
###
ARG PHP_VERSION=8.5.4
FROM php:${PHP_VERSION}-cli-alpine AS php_dev_container

# Composer environment variables:
# * default user is superuser (root), so allow them
# * put cache directory in a readable/writable location
# _Note_: When running `composer` in container, use `--no-cache` option
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/tmp/.composer/cache

# Install dependencies:
# * bash: for shell access and scripting
# * curl: for downloading build tools (box, micro.sfx)
# * libzip-dev: for composer packages that use ZIP archives
# _Note (Alpine)_: `--no-cache` includes `--update` and keeps image size minimal
#
# Then install PHP extensions
#
# _Note (Hadolint)_: No version locking, since Alpine only ever provides one version
# hadolint ignore=DL3018
RUN apk add --update --no-cache \
        bash \
        curl \
        libzip-dev \
        unzip

# Copy Composer binary from composer image
# _Note (Hadolint)_: False positive as `COPY` works with images too
# See: https://github.com/hadolint/hadolint/issues/197#issuecomment-1016595425
# hadolint ignore=DL3022
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Download Box (PHAR builder) — used by `make app-phar`
# https://github.com/box-project/box/releases
ARG BOX_VERSION=4.7.0
RUN curl --fail --location --silent \
        -o /usr/local/bin/box \
        "https://github.com/box-project/box/releases/download/${BOX_VERSION}/box.phar" \
    && chmod +x /usr/local/bin/box

# Download micro.sfx (static PHP runtime) for all target platforms — used by `make app-prep-release`
# https://static-php.dev/en/guide/precompiled.html
ARG PHP_VERSION
RUN for PLATFORM in linux-x86_64 linux-aarch64 macos-x86_64 macos-aarch64; do \
        curl --fail --location --silent \
            -o /tmp/micro.tar.gz \
            "https://dl.static-php.dev/static-php-cli/common/php-${PHP_VERSION}-micro-${PLATFORM}.tar.gz" \
        && tar xzf /tmp/micro.tar.gz -C /usr/local/lib/ \
        && mv /usr/local/lib/micro.sfx "/usr/local/lib/micro-${PLATFORM}.sfx" \
        && rm /tmp/micro.tar.gz; \
    done \
    && curl --fail --location --silent \
        -o /tmp/micro-win.zip \
        "https://dl.static-php.dev/static-php-cli/windows/spc-min/php-${PHP_VERSION}-micro-win.zip" \
    && unzip -q /tmp/micro-win.zip micro.sfx -d /usr/local/lib/ \
    && mv /usr/local/lib/micro.sfx /usr/local/lib/micro-windows-x86_64.sfx \
    && rm /tmp/micro-win.zip

WORKDIR /app

# Copy the application files (excluding those listed in .dockerignore)
COPY . .
