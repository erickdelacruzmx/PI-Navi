from django.apps import AppConfig

class LandingConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'NAVI.landing'
    
    def ready(self):
        import NAVI.landing.signals  # ACTIVAMOS LAS SEÑALES