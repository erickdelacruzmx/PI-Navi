# Despliegue robusto en instancia (Nginx + Gunicorn)

Esta guía deja NAVI corriendo como servicio con reinicio automático y proxy reverso.

## 1) Preparar instancia (Ubuntu)

```bash
sudo apt-get update
sudo apt-get install -y git python3-venv python3-pip nginx
```

## 2) Estructura recomendada

```bash
/var/www/navi/app     # código del proyecto
/var/www/navi/venv    # entorno virtual
```

Clona tu repo en `/var/www/navi/app`.

## 3) Variables de entorno

Copia `.env.example` a `.env` y ajusta al dominio real:

```env
DEBUG=False
ALLOWED_HOSTS=navicito.com,www.navicito.com
CSRF_TRUSTED_ORIGINS=https://navicito.com,https://www.navicito.com
DB_NAME=...
DB_USER=...
DB_PASSWORD=...
DB_HOST=...
DB_PORT=5432
DB_SSLMODE=require
SECURE_SSL_REDIRECT=True
```

Plantilla lista para copiar: `deployment/env/production.navicito.com.env`

## 4) Instalar dependencias y preparar Django

```bash
cd /var/www/navi/app
python3 -m venv /var/www/navi/venv
source /var/www/navi/venv/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
python manage.py migrate --noinput
python manage.py collectstatic --noinput
python manage.py check --deploy
```

## 5) Configurar Gunicorn como servicio systemd

```bash
sudo cp deployment/systemd/navi.service /etc/systemd/system/navi.service
sudo systemctl daemon-reload
sudo systemctl enable navi
sudo systemctl restart navi
sudo systemctl status navi
```

## 6) Configurar Nginx

`deployment/nginx/navi.conf` ya viene configurado para:

- `navicito.com`
- `www.navicito.com`

```bash
sudo cp deployment/nginx/navi.conf /etc/nginx/sites-available/navi.conf
sudo ln -sf /etc/nginx/sites-available/navi.conf /etc/nginx/sites-enabled/navi.conf
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl enable nginx
sudo systemctl restart nginx
```

## 7) Healthcheck

- URL interna app: `http://127.0.0.1:8000/health/`
- URL pública: `http://navicito.com/health/`

Debe responder JSON: `{"status": "ok"}`.

## 8) Operación segura (evitar caídas)

```bash
sudo systemctl restart navi
sudo systemctl restart nginx
sudo journalctl -u navi -f
sudo journalctl -u nginx -f
```

## 9) Habilitar HTTPS (Let's Encrypt)

Script listo con tu correo y dominios:

```bash
chmod +x deployment/scripts/enable_ssl_certbot.sh
./deployment/scripts/enable_ssl_certbot.sh
```

Este script:

- instala Certbot
- emite certificados para `navicito.com` y `www.navicito.com`
- fuerza redirección HTTP→HTTPS
- valida renovación automática

## 10) Actualización sin romper

```bash
cd /var/www/navi/app
git pull
source /var/www/navi/venv/bin/activate
pip install -r requirements.txt
python manage.py migrate --noinput
python manage.py collectstatic --noinput
python manage.py check --deploy
sudo systemctl restart navi
sudo systemctl reload nginx
```

## 11) Script opcional de bootstrap

Puedes usar `deployment/scripts/bootstrap_instance.sh` para automatizar la primera instalación.

## 12) Checklist de verificación post-deploy

```bash
curl -I https://navicito.com/
curl -I https://www.navicito.com/
curl https://navicito.com/health/
sudo systemctl is-active navi
sudo systemctl is-active nginx
```

Resultado esperado:

- ambos dominios responden `200` o `301` hacia HTTPS
- `/health/` devuelve `{"status":"ok"}`
- `navi` y `nginx` en estado `active`
