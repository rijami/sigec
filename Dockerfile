FROM php:8.1-apache

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
    && docker-php-ext-install \
        intl \
        zip \
        mbstring \
        pdo

# Driver PHP para SQL Server via PECL
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# ─── Configuración de Apache ─────────────────────────────────────────────────
RUN a2enmod rewrite \
    && sed -i 's!/var/www/html!/var/www/public!g' \
        /etc/apache2/sites-available/000-default.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ \
        s/AllowOverride None/AllowOverride All/' \
        /etc/apache2/apache2.conf

# ─── Composer 2 ──────────────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ─── Código de la aplicación ─────────────────────────────────────────────────
WORKDIR /var/www

COPY . .

# Instalar dependencias PHP en modo producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

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
