ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-cli

# pdo_mysql and gd are built from the PHP sources already in the image (mysqlnd, libpng and
# freetype are the only headers apt has to supply), so the apt step covers five packages: unzip,
# which Composer needs, and the two -dev pairs the gd build reads.
# The fallback branch covers Debian releases that have left the normal mirrors (bullseye, the
# base of php:7.4-cli): their security index still names packages whose pool files are gone, so
# the plain install fails with 404 and the archive mirror has to serve the packages instead.
RUN set -eux; \
    packages="unzip libpng-dev libfreetype6-dev"; \
    if ! apt-get update -qq || ! apt-get install -y --no-install-recommends $packages; then \
        . /etc/os-release; \
        rm -rf /var/lib/apt/lists/*; \
        printf 'deb http://archive.debian.org/debian %s main\n' "$VERSION_CODENAME" > /etc/apt/sources.list; \
        printf 'deb http://archive.debian.org/debian-security %s-security main\n' "$VERSION_CODENAME" >> /etc/apt/sources.list; \
        printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99archive; \
        apt-get update -qq; \
        apt-get install -y --no-install-recommends $packages; \
    fi; \
    docker-php-ext-configure gd --with-freetype; \
    docker-php-ext-install -j"$(nproc)" pdo_mysql gd; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# pcov is the coverage driver Pest's mutation testing requires. Only the php >= 8 stages get it:
# the 7.4 stage never runs Pest, and its Debian base cannot reach the normal apt mirrors.
RUN set -eux; \
    if [ "${PHP_VERSION%%.*}" -ge 8 ]; then \
        apt-get update -qq; \
        apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
        pecl install pcov; \
        docker-php-ext-enable pcov; \
        rm -rf /var/lib/apt/lists/* /tmp/pear; \
    fi
