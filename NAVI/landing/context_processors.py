from django.conf import settings
from django.contrib.sites.shortcuts import get_current_site
from django.db.utils import OperationalError, ProgrammingError

from allauth.socialaccount.models import SocialApp


def social_auth(request):
    providers = getattr(settings, 'SOCIALACCOUNT_PROVIDERS', {})
    google_provider = providers.get('google', {}) if isinstance(providers, dict) else {}
    google_app = google_provider.get('APP', {}) if isinstance(google_provider, dict) else {}
    google_from_settings = bool(google_app.get('client_id') and google_app.get('secret'))

    google_social_configured = google_from_settings

    try:
        current_site = get_current_site(request)
        google_from_db = SocialApp.objects.filter(
            provider='google',
            sites=current_site,
        ).exists()
        google_social_configured = google_social_configured or google_from_db
    except (OperationalError, ProgrammingError):
        pass

    return {
        'google_social_configured': google_social_configured,
    }
