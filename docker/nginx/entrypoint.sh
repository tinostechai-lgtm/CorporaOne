SSL_CERT_PATH="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
SSL_KEY_PATH="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"

if [ -f "$SSL_CERT_PATH" ] && [ -f "$SSL_KEY_PATH" ]; then
    echo "SSL certificate and key found. Starting Nginx..."
    cp  /etc/nginx/includes/ssl.conf /etc/nginx/includes/server.conf
else
    echo "SSL certificate or key not found. Starting Nginx in non-SSL mode..."
    cp /etc/nginx/includes/init.conf /etc/nginx/includes/server.conf
fi

sh /docker-entrypoint.sh

exec "$@" 
