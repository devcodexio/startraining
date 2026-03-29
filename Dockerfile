FROM php:8.2-apache

# 🔹 Instalar dependencias del sistema (AQUÍ está el fix)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 🔹 Ahora sí instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf


# 🔹 Cambiar DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 🔹 Copiar proyecto
COPY . /var/www/html/

# 🔹 Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
