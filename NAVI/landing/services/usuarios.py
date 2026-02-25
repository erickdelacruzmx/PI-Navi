import logging

from django.db import DatabaseError
from django.utils import timezone

from NAVI.landing.models import Usuario

logger = logging.getLogger(__name__)


def sincronizar_usuario_auth(user):
    if not user.email:
        return

    try:
        usuario, _ = Usuario.objects.get_or_create(
            correo=user.email,
            defaults={
                'nombres': user.first_name or '',
                'apellidos': user.last_name or '',
                'contrasena': user.password,
                'fecharegistro': timezone.now(),
                'suscrito': False,
            },
        )

        cambios = []
        nuevo_nombre = user.first_name or ''
        nuevo_apellido = user.last_name or ''

        if usuario.nombres != nuevo_nombre:
            usuario.nombres = nuevo_nombre
            cambios.append('nombres')

        if usuario.apellidos != nuevo_apellido:
            usuario.apellidos = nuevo_apellido
            cambios.append('apellidos')

        if usuario.contrasena != user.password:
            usuario.contrasena = user.password
            cambios.append('contrasena')

        if cambios:
            usuario.save(update_fields=cambios)
    except (DatabaseError, ValueError, TypeError) as exc:
        logger.exception("No se pudo sincronizar auth_user -> usuarios para %s: %s", user.email, exc)
