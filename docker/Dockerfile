ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-cli

# pdo_mysql is built from the PHP sources already in the image (mysqlnd is bundled), so the
# only reason to touch apt is Composer's need for an unzip implementation.
# The fallback branch covers Debian releases that have left the normal mirrors (bullseye, the
# base of php:7.4-cli): their security index still names packages whose pool files are gone, so
# the plain install fails with 404 and the archive mirror has to serve the packages instead.
RUN set -eux; \
    if ! apt-get update -qq || ! apt-get install -y --no-install-recommends unzip; then \
        . /etc/os-release; \
        rm -rf /var/lib/apt/lists/*; \
        printf 'deb http://archive.debian.org/debian %s main\n' "$VERSION_CODENAME" > /etc/apt/sources.list; \
        printf 'deb http://archive.debian.org/debian-security %s-security main\n' "$VERSION_CODENAME" >> /etc/apt/sources.list; \
        printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99archive; \
        apt-get update -qq; \
        apt-get install -y --no-install-recommends unzip; \
    fi; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
