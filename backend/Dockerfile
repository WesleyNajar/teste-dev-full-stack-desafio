FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    supervisor \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do projeto
COPY . /var/www

# Instalar dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs || composer update --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Configurar permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Criar arquivo .env se não existir
RUN if [ ! -f .env ]; then cp env.example .env; fi

# Expor porta 9000
EXPOSE 9000

# Comando padrão - usar servidor interno do PHP
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]
