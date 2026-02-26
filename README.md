# NAVI - Guía de Inicio Rápido

## Instalación

### 1. Clonar Repositorio y Crear Entorno Virtual

```powershell
cd C:\Users\erick\OneDrive\Escritorio\Navi\PI-Navi
python -m venv venv
.\venv\Scripts\Activate.ps1
```

### 2. Instalar Dependencias

```powershell
pip install -r requirements.txt
```

### 3. Configurar Variables de Entorno

Copia `.env.example` a `.env` y ajusta según necesites:

```powershell
Copy-Item .env.example .env
```

**Configuración por defecto** (SQLite, desarrollo):
```env
USE_SQLITE=True
DEBUG=True
SECRET_KEY=django-insecure-change-me-in-production
```

### 4. Aplicar Migraciones

```powershell
python manage.py migrate
```

### 5. Crear Usuario Administrador (Opcional)

```powershell
python manage.py createsuperuser
```

### 6. Iniciar Servidor de Desarrollo

```powershell
python manage.py runserver
```

**Accede a**: http://localhost:8000

## Rutas Principales

| Ruta | Descripción | Autenticación |
|------|-------------|---------------|
| `/` | Página principal | No |
| `/app/` | Aplicación principal | Sí |
| `/perfil/` | Perfil de usuario | Sí |
| `/configuracion/` | Redirige a `app` en pestaña Configuración (`/app/?section=configuracion`) | Sí |
| `/accounts/login/` | Iniciar sesión | No |
| `/accounts/signup/` | Registro | No |
| `/admin/` | Panel admin Django | Superusuario |

## Estructura del Proyecto

```
PI-Navi/
├── manage.py              # Comando principal Django
├── requirements.txt       # Dependencias Python
├── .env                   # Variables de entorno (no versionar)
├── db.sqlite3            # Base de datos SQLite (desarrollo)
├── NAVI/                 # Configuración del proyecto
│   ├── settings.py       # Configuración Django
│   ├── urls.py          # URLs principales
│   └── wsgi.py          # Deployment WSGI
├── landing/             # App principal
│   ├── views.py         # Vistas
│   ├── urls.py          # URLs de la app
│   ├── models.py        # Modelos de BD
│   ├── forms.py         # Formularios
│   └── static/          # Archivos estáticos
├── templates/           # Plantillas HTML
│   ├── base.html        # Template base
│   ├── landing/         # Templates de landing
│   └── account/         # Templates de autenticación
├── static/              # Estáticos globales
└── docs/                # Documentación

```

## Desarrollo

### Comandos Útiles

```powershell
# Validar proyecto sin errores
python manage.py check

# Crear migraciones nuevas
python manage.py makemigrations

# Ver migraciones pendientes
python manage.py showmigrations

# Shell interactivo Django
python manage.py shell

# Recolectar archivos estáticos (producción)
python manage.py collectstatic
```

### Variables de Entorno Importantes

| Variable | Descripción | Desarrollo | Producción |
|----------|-------------|------------|------------|
| `DEBUG` | Modo debug | `True` | `False` |
| `USE_SQLITE` | Usar SQLite | `True` | `False` |
| `SECRET_KEY` | Clave secreta Django | Cualquiera | Única y segura |
| `ALLOWED_HOSTS` | Dominios permitidos | `localhost,127.0.0.1` | Dominio real |

## Base de Datos

Por defecto usa **SQLite** para desarrollo rápido.

Para migrar a **PostgreSQL**: ver [docs/setup_base_datos.md](docs/setup_base_datos.md)

## Despliegue en Producción (Nginx + Gunicorn)

Se añadieron plantillas listas para instancia Linux:

- `deployment/gunicorn/gunicorn.conf.py`
- `deployment/systemd/navi.service`
- `deployment/nginx/navi.conf`
- `deployment/scripts/bootstrap_instance.sh`
- `deployment/scripts/enable_ssl_certbot.sh`
- `deployment/env/production.navicito.com.env`

Guía completa: `docs/deploy_nginx_gunicorn.md`

## Migrations Aplicadas

✅ Todas las migraciones iniciales aplicadas:
- `contenttypes`, `auth`, `account`, `admin`
- `landing`, `sessions`, `sites`, `socialaccount`

## Próximos Pasos

1. **Configurar PostgreSQL**: Ver guía en `docs/setup_base_datos.md`
2. **Personalizar templates**: Editar archivos en `templates/`
3. **Agregar contenido estático**: Colocar en `landing/static/` o `static/`
4. **Configurar autenticación social**: Descomentar proveedores en `settings.py`

## Soporte

- **Documentación Django**: https://docs.djangoproject.com/
- **Django Allauth**: https://django-allauth.readthedocs.io/
- **Arquitectura del proyecto**: `docs/arquitectura_mvt.md`
- **Migración PHP→Django**: `docs/migracion_templates_php.md`
