#!/bin/sh
# Ajusta permissões do storage antes de iniciar os serviços
chown -R www-data:www-data /var/www/html/storage
# Executa o supervisor que gerenciará o Apache e a Fila
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
