from allauth.socialaccount.adapter import DefaultSocialAccountAdapter
from django.contrib.auth import get_user_model


class NaviSocialAccountAdapter(DefaultSocialAccountAdapter):
    def pre_social_login(self, request, sociallogin):
        if sociallogin.is_existing:
            return

        email = (sociallogin.user.email or "").strip().lower()
        if not email:
            return

        UserModel = get_user_model()
        existing_user = UserModel.objects.filter(email__iexact=email).first()
        if not existing_user:
            return

        sociallogin.connect(request, existing_user)
