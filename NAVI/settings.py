"""
Django settings for NAVI project.
"""

from pathlib import Path
import os

BASE_DIR = Path(__file__).resolve().parent.parent


def _load_env_file(env_path):
    if not env_path.exists():
        return
    for line in env_path.read_text(encoding='utf-8').splitlines():
        value = line.strip()
        if not value or value.startswith('#') or '=' not in value:
            continue
        key, raw = value.split('=', 1)
        os.environ.setdefault(key.strip(), raw.strip().strip('"').strip("'"))


_load_env_file(BASE_DIR / '.env')

SECRET_KEY = os.getenv('SECRET_KEY', 'django-insecure-78nleun9=hy%^y+#dg3zjx8tsecf1kxflu#zj$^mb87$e-4v9o')
DEBUG = os.getenv('DEBUG', 'True').lower() in ('1', 'true', 'yes', 'on')
ALLOWED_HOSTS = [h.strip() for h in os.getenv('ALLOWED_HOSTS', 'localhost,127.0.0.1').split(',') if h.strip()]


def _build_csrf_trusted_origins_from_hosts(hosts):
    trusted = []
    for host in hosts:
        normalized = host.strip().lstrip('.')
        if not normalized or normalized in ('localhost', '127.0.0.1', '[::1]'):
            continue
        trusted.append(f'https://{normalized}')
    return trusted


CSRF_TRUSTED_ORIGINS = [o.strip() for o in os.getenv('CSRF_TRUSTED_ORIGINS', '').split(',') if o.strip()]
if not CSRF_TRUSTED_ORIGINS and not DEBUG:
    # Fallback robusto para evitar 403 por origen en despliegues donde falte la variable en .env.
    CSRF_TRUSTED_ORIGINS = _build_csrf_trusted_origins_from_hosts(ALLOWED_HOSTS)
INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'django.contrib.sites',
    
    # Mis apps
    'NAVI.landing.apps.LandingConfig',
    
    # Allauth
    'allauth',
    'allauth.account',
    'allauth.socialaccount',
    
    # Proveedores sociales
    'allauth.socialaccount.providers.google',
    # 'allauth.socialaccount.providers.facebook',
]

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'allauth.account.middleware.AccountMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'NAVI.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.template.context_processors.csrf',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                'NAVI.landing.context_processors.social_auth',
            ],
        },
    },
]

WSGI_APPLICATION = 'NAVI.wsgi.application'

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'NAME': os.getenv('DB_NAME', 'navi'),
        'USER': os.getenv('DB_USER', 'postgres'),
        'PASSWORD': os.getenv('DB_PASSWORD', 'cisco'),
        'HOST': os.getenv('DB_HOST', 'localhost'),
        'PORT': os.getenv('DB_PORT', '5432'),
        'CONN_MAX_AGE': int(os.getenv('DB_CONN_MAX_AGE', '60')),
    }
}

if os.getenv('DB_SSLMODE'):
    DATABASES['default']['OPTIONS'] = {
        'sslmode': os.getenv('DB_SSLMODE')
    }

AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator'},
    {'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator'},
    {'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator'},
]

# Configuración de autenticación
SITE_ID = 1

AUTHENTICATION_BACKENDS = [
    'django.contrib.auth.backends.ModelBackend',
    'allauth.account.auth_backends.AuthenticationBackend',
]

# Configuración de allauth
ACCOUNT_LOGIN_METHODS = {'email'}
ACCOUNT_SIGNUP_FIELDS = ['email*', 'password1*', 'password2*']
ACCOUNT_UNIQUE_EMAIL = True
ACCOUNT_EMAIL_VERIFICATION = 'none'
ACCOUNT_LOGOUT_ON_GET = True
ACCOUNT_SESSION_REMEMBER = True
SOCIALACCOUNT_LOGIN_ON_GET = True
SOCIALACCOUNT_ADAPTER = 'NAVI.landing.adapters.NaviSocialAccountAdapter'
SOCIALACCOUNT_EMAIL_AUTHENTICATION = True
SOCIALACCOUNT_EMAIL_AUTHENTICATION_AUTO_CONNECT = True

GOOGLE_CLIENT_ID = os.getenv('GOOGLE_CLIENT_ID', '').strip()
GOOGLE_CLIENT_SECRET = os.getenv('GOOGLE_CLIENT_SECRET', '').strip()
GEMINI_API_KEY = os.getenv('GEMINI_API_KEY', '').strip()
GEMINI_MODEL = os.getenv('GEMINI_MODEL', 'gemini-1.5-flash').strip()
GEMINI_TIMEOUT_SECONDS = int(os.getenv('GEMINI_TIMEOUT_SECONDS', '20'))
GEMINI_TEMPERATURE = float(os.getenv('GEMINI_TEMPERATURE', '0.45'))
GEMINI_MAX_OUTPUT_TOKENS = int(os.getenv('GEMINI_MAX_OUTPUT_TOKENS', '220'))
GEMINI_TTS_ENABLED = os.getenv('GEMINI_TTS_ENABLED', 'True').lower() in ('1', 'true', 'yes', 'on')
GEMINI_TTS_MODEL = os.getenv('GEMINI_TTS_MODEL', 'gemini-2.5-flash-preview-tts').strip()
GEMINI_TTS_FALLBACK_MODELS = [
    model_name.strip()
    for model_name in os.getenv('GEMINI_TTS_FALLBACK_MODELS', 'gemini-2.5-pro-preview-tts').split(',')
    if model_name.strip()
]
GEMINI_TTS_TIMEOUT_SECONDS = int(os.getenv('GEMINI_TTS_TIMEOUT_SECONDS', '25'))
GEMINI_TTS_MAX_CHARS = int(os.getenv('GEMINI_TTS_MAX_CHARS', '900'))
NAVI_MAX_HISTORY_MESSAGES = int(os.getenv('NAVI_MAX_HISTORY_MESSAGES', '8'))
NAVI_MAX_HISTORY_CHARS_PER_MESSAGE = int(os.getenv('NAVI_MAX_HISTORY_CHARS_PER_MESSAGE', '420'))
NAVI_DAILY_TOKEN_BUDGET = int(os.getenv('NAVI_DAILY_TOKEN_BUDGET', '14000'))
NAVI_DAILY_REQUEST_BUDGET = int(os.getenv('NAVI_DAILY_REQUEST_BUDGET', '80'))
NAVI_MIN_SECONDS_BETWEEN_REQUESTS = int(os.getenv('NAVI_MIN_SECONDS_BETWEEN_REQUESTS', '2'))

SOCIALACCOUNT_PROVIDERS = {
    'google': {
        'SCOPE': ['profile', 'email'],
        'AUTH_PARAMS': {'access_type': 'online'},
    }
}

if GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET:
    SOCIALACCOUNT_PROVIDERS['google']['APP'] = {
        'client_id': GOOGLE_CLIENT_ID,
        'secret': GOOGLE_CLIENT_SECRET,
        'key': '',
    }

LOGIN_REDIRECT_URL = 'landing:app'
LOGOUT_REDIRECT_URL = '/'

ACCOUNT_DEFAULT_HTTP_PROTOCOL = 'https' if not DEBUG else 'http'
DEFAULT_FROM_EMAIL = os.getenv('DEFAULT_FROM_EMAIL', 'Navi <no-reply@navicito.com>')

EMAIL_HOST = os.getenv('EMAIL_HOST', 'smtp.gmail.com')
EMAIL_PORT = int(os.getenv('EMAIL_PORT', '587'))
EMAIL_HOST_USER = os.getenv('EMAIL_HOST_USER', '')
EMAIL_HOST_PASSWORD = os.getenv('EMAIL_HOST_PASSWORD', '')
EMAIL_USE_TLS = os.getenv('EMAIL_USE_TLS', 'True').lower() in ('1', 'true', 'yes', 'on')
EMAIL_USE_SSL = os.getenv('EMAIL_USE_SSL', 'False').lower() in ('1', 'true', 'yes', 'on')

EMAIL_BACKEND = os.getenv('EMAIL_BACKEND', '').strip()
if not EMAIL_BACKEND:
    if EMAIL_HOST_USER and EMAIL_HOST_PASSWORD:
        EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
    elif DEBUG:
        EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'
    else:
        EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'

LANGUAGE_CODE = 'es-mx'
TIME_ZONE = 'America/Mexico_City'
USE_I18N = True
USE_TZ = True

STATIC_URL = '/static/'
STATICFILES_DIRS = [BASE_DIR / 'static']
STATIC_ROOT = BASE_DIR / 'staticfiles'

MEDIA_URL = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'

DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'

# Producción detrás de Nginx/Gunicorn
USE_X_FORWARDED_HOST = True
SECURE_PROXY_SSL_HEADER = ('HTTP_X_FORWARDED_PROTO', 'https')

# Cookies compartidas entre subdominios (ej: navicito.com y www.navicito.com).
SESSION_COOKIE_DOMAIN = os.getenv('SESSION_COOKIE_DOMAIN', '').strip() or None
CSRF_COOKIE_DOMAIN = os.getenv('CSRF_COOKIE_DOMAIN', '').strip() or None
SESSION_COOKIE_SAMESITE = os.getenv('SESSION_COOKIE_SAMESITE', 'Lax').strip() or 'Lax'
CSRF_COOKIE_SAMESITE = os.getenv('CSRF_COOKIE_SAMESITE', 'Lax').strip() or 'Lax'

if not DEBUG:
    SECURE_SSL_REDIRECT = os.getenv('SECURE_SSL_REDIRECT', 'True').lower() in ('1', 'true', 'yes', 'on')
    SESSION_COOKIE_SECURE = True
    CSRF_COOKIE_SECURE = True
    SESSION_COOKIE_HTTPONLY = True
    CSRF_COOKIE_HTTPONLY = os.getenv('CSRF_COOKIE_HTTPONLY', 'False').lower() in ('1', 'true', 'yes', 'on')
    SECURE_BROWSER_XSS_FILTER = True
    SECURE_CONTENT_TYPE_NOSNIFF = True
    X_FRAME_OPTIONS = 'DENY'
    SECURE_HSTS_SECONDS = int(os.getenv('SECURE_HSTS_SECONDS', '31536000'))
    SECURE_HSTS_INCLUDE_SUBDOMAINS = os.getenv('SECURE_HSTS_INCLUDE_SUBDOMAINS', 'True').lower() in ('1', 'true', 'yes', 'on')
    SECURE_HSTS_PRELOAD = os.getenv('SECURE_HSTS_PRELOAD', 'True').lower() in ('1', 'true', 'yes', 'on')
else:
    SECURE_SSL_REDIRECT = False
    SECURE_HSTS_SECONDS = 0

if DEBUG:
    import mimetypes
    mimetypes.add_type("application/javascript", ".js", True)

# Custom forms
ACCOUNT_FORMS = {
    'signup': 'NAVI.landing.forms.CustomSignupForm',
}

# Configuración de Correo Electrónico (Recuperación de contraseña)
EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
EMAIL_HOST = 'smtp.gmail.com'
EMAIL_PORT = 587
EMAIL_USE_TLS = True
EMAIL_HOST_USER = os.getenv('EMAIL_USER')
EMAIL_HOST_PASSWORD = os.getenv('EMAIL_PASSWORD')
DEFAULT_FROM_EMAIL = f"Equipo Navi <{EMAIL_HOST_USER}>"
