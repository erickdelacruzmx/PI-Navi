#!/usr/bin/env bash
set -euo pipefail

DOMAIN_ROOT="navicito.com"
DOMAIN_WWW="www.navicito.com"
EMAIL="erickdelacruz.mx@gmail.com"

sudo apt-get update
sudo apt-get install -y certbot python3-certbot-nginx

sudo nginx -t
sudo systemctl reload nginx

sudo certbot --nginx \
  -d "$DOMAIN_ROOT" \
  -d "$DOMAIN_WWW" \
  --non-interactive \
  --agree-tos \
  --email "$EMAIL" \
  --redirect

sudo certbot renew --dry-run

echo "SSL habilitado para $DOMAIN_ROOT y $DOMAIN_WWW"
