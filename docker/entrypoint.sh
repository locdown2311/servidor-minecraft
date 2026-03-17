#!/bin/sh
# Ajusta permissões do storage antes de iniciar os serviços
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Gera a APP_KEY se não existir
php /var/www/html/artisan key:generate --force --no-interaction

# Cria o link simbólico do storage
php /var/www/html/artisan storage:link --force --no-interaction

# Roda as migrations
php /var/www/html/artisan migrate --force --no-interaction

# Limpa caches
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Executa o supervisor que gerenciará o Apache e a Fila
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
