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

**Configuración por defecto** (PostgreSQL):
```env
DEBUG=True
SECRET_KEY=django-insecure-change-me-in-production
DB_NAME=navi
DB_USER=postgres
DB_PASSWORD=tu_password
DB_HOST=localhost
DB_PORT=5432
GEMINI_API_KEY=tu_api_key_de_gemini
GEMINI_MODEL=gemini-1.5-flash
GEMINI_TIMEOUT_SECONDS=20
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
| `SECRET_KEY` | Clave secreta Django | Cualquiera | Única y segura |
| `ALLOWED_HOSTS` | Dominios permitidos | `localhost,127.0.0.1` | Dominio real |
| `DB_NAME` | Nombre BD PostgreSQL | `navi` | BD de producción |
| `DB_USER` | Usuario BD | `postgres` | Usuario de producción |
| `DB_PASSWORD` | Password BD | Local | Secreto seguro |
| `DB_HOST` | Host de PostgreSQL | `localhost` | Endpoint real |
| `DB_PORT` | Puerto de PostgreSQL | `5432` | Puerto real |
| `GEMINI_API_KEY` | API key para agente Navi | Requerida | Requerida |
| `GEMINI_MODEL` | Modelo Gemini a usar | `gemini-1.5-flash` | Ajustar por costo/calidad |
| `GEMINI_TIMEOUT_SECONDS` | Timeout de respuesta IA | `20` | `20-30` |

## Navi Agent (Fase A)

La Fase A agrega un chat real de Navi con Gemini.

- Endpoint historial: `GET /api/navi/conversation/`
- Endpoint chat: `POST /api/navi/chat/`
- Endpoint preferencias de voz: `POST /api/navi/preferences/`
- Endpoint TTS natural: `POST /api/navi/tts/`
- Persistencia: tablas `navi_conversations` y `navi_messages`
- Preferencias persistidas: tabla `navi_voice_preferences`
- Interaccion principal por voz desde el circulo de Navi:
  - Reconocimiento de voz del navegador (SpeechRecognition/webkitSpeechRecognition)
  - Respuesta auditiva automatica (SpeechSynthesis)
  - Campo de texto como respaldo si el navegador no soporta voz
- Tutorial de primera vez en audio (persistido por usuario)

Comandos de voz soportados:

- `repetir`
- `tutorial`, `ayuda`
- `voz suave`, `voz infantil`
- `voz clara`, `voz tutor`
- `detener`, `parar`, `silencio`
- `hablar mas lento`
- `hablar mas rapido`
- `modo solo audio`, `desactivar modo solo audio`
- `ir a juegos`, `ir a biblioteca`, `ir a estadisticas`, `ir a configuracion`, `ir a perfil`, `ir a navi`

Nota: para que el chat responda, `GEMINI_API_KEY` debe estar configurada.

Variables recomendadas para TTS natural:

- `GEMINI_TTS_ENABLED=True`
- `GEMINI_TTS_MODEL=gemini-2.5-flash-preview-tts`
- `GEMINI_TTS_FALLBACK_MODELS=gemini-2.5-pro-preview-tts`
- `GEMINI_TTS_TIMEOUT_SECONDS=25`
- `GEMINI_TTS_MAX_CHARS=900`

Perfiles de voz disponibles:

- `suave`: estilo infantil, calido y pausado.
- `clara`: estilo neutro e instructivo para tutores.

## Base de Datos

Usa **PostgreSQL** por configuración del proyecto.

Para preparar PostgreSQL en servidor: ver [docs/setup_base_datos.md](docs/setup_base_datos.md)

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
