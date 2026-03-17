# Use a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Argumentos (podem ser passados via --build-arg)
ARG user=www-data
ARG uid=1000

# Instala dependências do sistema e extensões do PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm \
    supervisor

# Limpa o cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP necessárias para o Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_sqlite soap
RUN pecl install redis && docker-php-ext-enable redis

# Aumenta os limites de envio de arquivos e memória do PHP para 50MB
RUN echo "upload_max_filesize = 50M\npost_max_size = 50M\nmemory_limit = 256M" > /usr/local/etc/php/conf.d/uploads.ini

# Configura e habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala o Composer latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Adicionar o grupo docker para acessar o socket montado (gerenciar containers Minecraft)
RUN groupadd -g 999 docker && usermod -aG docker www-data

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto para o container
COPY . /var/www/html

# Ajusta o DocumentRoot do Apache para a pasta public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instala dependências do PHP (ignora platform reqs caso falte algo na imagem base que o sail usaria)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Instala dependências do Node e compila os assets (Vite)
RUN npm install
RUN npm run build

# Ajusta as permissões gerais
RUN chown -R www-data:www-data /var/www/html

# Garante que as pastas cruciais do Laravel tenham permissão de escrita
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Cria pasta storage persistente
RUN mkdir -p /var/www/html/storage/app/public

EXPOSE 80

# Cria pasta para os logs do supervisor e instala cron
RUN mkdir -p /var/log/supervisor
RUN apt-get update && apt-get install -y cron && rm -rf /var/lib/apt/lists/*

# Adiciona cron do Laravel scheduler
RUN echo "* * * * * www-data cd /var/www/html && php artisan schedule:run >> /var/www/html/storage/logs/scheduler.log 2>&1" > /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler

# Configura o Supervisor e entrypoint (arquivos externos)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
