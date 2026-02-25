from django.contrib.auth.models import User
from django.db.models.signals import post_save
from django.dispatch import receiver
from NAVI.landing.services import sincronizar_usuario_auth


@receiver(post_save, sender=User)
def sincronizar_usuario_en_tabla(sender, instance, **kwargs):
    """Sincroniza auth_user con la tabla usuarios sin bloquear login/registro."""
    sincronizar_usuario_auth(instance)
