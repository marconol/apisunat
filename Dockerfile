FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Habilitar DOM (para parsing HTML)
RUN docker-php-ext-install dom

# Activar rewrite (opcional)
RUN a2enmod rewrite

# Copiar tu código PHP al servidor web
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
