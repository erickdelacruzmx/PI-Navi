#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="/var/www/navi/app"
VENV_PATH="/var/www/navi/venv"
SERVICE_FILE="/etc/systemd/system/navi.service"
NGINX_SITE="/etc/nginx/sites-available/navi.conf"

sudo apt-get update
sudo apt-get install -y python3-venv python3-pip nginx

sudo mkdir -p /var/www/navi
sudo chown -R "$USER":"$USER" /var/www/navi

if [ ! -d "$VENV_PATH" ]; then
  python3 -m venv "$VENV_PATH"
fi

source "$VENV_PATH/bin/activate"
pip install --upgrade pip
pip install -r "$APP_ROOT/requirements.txt"

cd "$APP_ROOT"
python manage.py migrate --noinput
python manage.py collectstatic --noinput
python manage.py check --deploy

sudo cp "$APP_ROOT/deployment/systemd/navi.service" "$SERVICE_FILE"
sudo systemctl daemon-reload
sudo systemctl enable navi
sudo systemctl restart navi

sudo cp "$APP_ROOT/deployment/nginx/navi.conf" "$NGINX_SITE"
sudo ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/navi.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl enable nginx
sudo systemctl restart nginx

echo "Despliegue base finalizado. Revisa: systemctl status navi nginx"
