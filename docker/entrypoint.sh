#!/bin/sh

# Ajusta permissões do socket do Docker (dinâmico)
if [ -S /var/run/docker.sock ]; then
    DOCKER_GID=$(stat -c '%g' /var/run/docker.sock)
    groupadd -for -g "$DOCKER_GID" docker_host
    usermod -aG docker_host www-data
    echo "Grupamento 'docker_host' ($DOCKER_GID) configurado para www-data"
fi

# Cria .env a partir do .env.example se não existir
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    echo ".env criado a partir do .env.example"
fi

# Ajusta permissões do .env
chown www-data:www-data /var/www/html/.env
chmod 644 /var/www/html/.env

# Ajusta permissões do storage antes de iniciar os serviços
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Gera a APP_KEY se não existir
php /var/www/html/artisan key:generate --force --no-interaction

# Cria o link simbólico do storage
php /var/www/html/artisan storage:link --force --no-interaction

# Espera o MySQL ficar pronto se necessário
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Aguardando MySQL..."
    until php artisan db:monitor; do # This is a simple check, or just a sleep
        sleep 2
    done
fi

# Roda as migrations e seeders
php /var/www/html/artisan migrate --force --no-interaction
php /var/www/html/artisan db:seed --force --no-interaction

# Limpa caches
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Executa o supervisor que gerenciará o Apache e a Fila
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
