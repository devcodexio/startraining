FROM php:8.2-apache

# 🔹 Instalar dependencias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 🔹 Extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql

# 🔹 Activar mod_rewrite (ANTES)
RUN a2enmod rewrite

# 🔹 Permitir .htaccess
RUN sed -i 's/AllowOverride None/
