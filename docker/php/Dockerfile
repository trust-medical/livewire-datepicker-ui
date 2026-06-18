# Lightweight PHP toolchain for the package: Composer, Pest, Larastan, Pint
# and the Testbench dev server. No database/cache services are required.
FROM php:8.3-cli-alpine

# Build deps are added, used to compile the extensions we need, then removed
# to keep the final image small.
RUN set -eux; \
    apk add --no-cache git unzip libzip icu-libs oniguruma; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libzip-dev icu-dev oniguruma-dev; \
    docker-php-ext-install -j"$(nproc)" zip intl mbstring; \
    apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Non-root user so bind-mounted files keep host ownership.
ARG UID=1000
ARG GID=1000
RUN set -eux; \
    addgroup -g "${GID}" -S app; \
    adduser -u "${UID}" -S -G app app; \
    mkdir -p /home/app/.composer; \
    chown -R app:app /home/app

# COMPOSER_ROOT_VERSION labels the untagged root package during development so
# Composer does not warn and fall back to '1.0.0'. It only affects the root
# package's reported version, not dependency resolution. Released versions come
# from Git tags, so this is a dev-only convenience.
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1 \
    COMPOSER_HOME=/home/app/.composer \
    COMPOSER_ROOT_VERSION=dev-main \
    PATH="/app/vendor/bin:${PATH}"

WORKDIR /app
USER app

CMD ["php", "-a"]
