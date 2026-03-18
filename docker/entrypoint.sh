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

# Limpa cache de configuração SEM tocar no banco
# (remove os arquivos de cache diretamente para evitar conexão ao DB)
rm -f /var/www/html/bootstrap/cache/config.php
rm -f /var/www/html/bootstrap/cache/routes-v7.php
rm -f /var/www/html/bootstrap/cache/services.php
rm -f /var/www/html/bootstrap/cache/packages.php

# Gera a APP_KEY se não existir
php /var/www/html/artisan key:generate --force --no-interaction

# Cria o link simbólico do storage
php /var/www/html/artisan storage:link --force --no-interaction

# Espera o MariaDB ficar pronto se necessário
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Aguardando MariaDB..."
    # Testa a conexão TCP diretamente, sem depender do artisan
    MAX_RETRIES=3
    RETRY=0
    until nc -z -w2 mariadb 3306 2>/dev/null; do
        RETRY=$((RETRY + 1))
        if [ $RETRY -ge $MAX_RETRIES ]; then
            echo "ERRO: MariaDB não respondeu após $MAX_RETRIES tentativas"
            break
        fi
        echo "  Tentativa $RETRY/$MAX_RETRIES - aguardando MariaDB na porta 3306..."
        sleep 2
    done
    echo "MariaDB disponível!"
fi

# Roda as migrations e seeders
php /var/www/html/artisan migrate --force --no-interaction
php /var/www/html/artisan db:seed --force --no-interaction

# Reconstrói os caches (APÓS o banco estar pronto)
php /var/www/html/artisan config:cache
php /var/www/html/artisan route:cache
php /var/www/html/artisan view:cache

# Executa o supervisor que gerenciará o Apache e a Fila
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
