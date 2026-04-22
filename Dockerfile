FROM php:8.2-apache

LABEL maintainer="SIGEC - Mallamas EPS"

# ─── Dependencias del sistema ───────────────────────────────────────────────
RUN apt-get update && apt-get install -y \
    curl \
    gnupg2 \
    apt-transport-https \
    ca-certificates \
    git \
    libicu-dev \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libgd-dev \
    unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

# ─── Driver ODBC de Microsoft para SQL Server (Debian 12 / Bookworm) ────────
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# ─── Extensiones PHP ─────────────────────────────────────────────────────────
RUN docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        zip \
        mbstring \
        pdo \
        gd

# Driver PHP para SQL Server via PECL (5.12.0 = compatible con PHP 8.1 y 8.2, máx < 8.3)
RUN pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0 \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# ─── Configuración de Apache ─────────────────────────────────────────────────
RUN a2enmod rewrite \
    && sed -i 's!/var/www/html!/var/www/public!g' \
        /etc/apache2/sites-available/000-default.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ \
        s/AllowOverride None/AllowOverride All/' \
        /etc/apache2/apache2.conf

# ─── Configuración PHP para producción ───────────────────────────────────────
RUN { \
    echo 'display_errors = Off'; \
    echo 'display_startup_errors = Off'; \
    echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/log/sigec/php-errors.log'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
} > /usr/local/etc/php/conf.d/sigec-production.ini

# ─── Composer 2 ──────────────────────────────────────────────────────────────

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ─── Código de la aplicación ─────────────────────────────────────────────────
WORKDIR /var/www

COPY . .

# Instalar dependencias PHP en modo producción
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    --ignore-platform-req=php \
    --ignore-platform-req=ext-gd

# ─── Directorios requeridos y permisos ───────────────────────────────────────
RUN mkdir -p data/cache data/logs data/sessions /var/log/sigec \
    && chown -R www-data:www-data data/ /var/log/sigec \
    && chmod -R 775 data/ /var/log/sigec

# ─── Entrypoint ──────────────────────────────────────────────────────────────
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
