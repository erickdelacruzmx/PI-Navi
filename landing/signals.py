import logging

from django.contrib.auth.models import User
from django.db import DatabaseError
from django.db.models.signals import post_save
from django.dispatch import receiver
from django.utils import timezone

from .models import Usuario

logger = logging.getLogger(__name__)


@receiver(post_save, sender=User)
def sincronizar_usuario_en_tabla(sender, instance, **kwargs):
    """Sincroniza auth_user con la tabla usuarios sin bloquear login/registro."""
    if not instance.email:
        return

    try:
        usuario, _ = Usuario.objects.get_or_create(
            correo=instance.email,
            defaults={
                'nombres': instance.first_name or '',
                'apellidos': instance.last_name or '',
                'contrasena': instance.password,
                'fecharegistro': timezone.now(),
                'suscrito': False,
            },
        )

        cambios = []
        nuevo_nombre = instance.first_name or ''
        nuevo_apellido = instance.last_name or ''

        if usuario.nombres != nuevo_nombre:
            usuario.nombres = nuevo_nombre
            cambios.append('nombres')

        if usuario.apellidos != nuevo_apellido:
            usuario.apellidos = nuevo_apellido
            cambios.append('apellidos')

        if usuario.contrasena != instance.password:
            usuario.contrasena = instance.password
            cambios.append('contrasena')

        if cambios:
            usuario.save(update_fields=cambios)
    except (DatabaseError, ValueError, TypeError) as exc:
        logger.exception("No se pudo sincronizar auth_user -> usuarios para %s: %s", instance.email, exc)
