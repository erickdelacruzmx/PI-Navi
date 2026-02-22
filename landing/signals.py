from django.db.models.signals import post_save
from django.dispatch import receiver
from django.contrib.auth.models import User
from .models import Usuario
from datetime import datetime

@receiver(post_save, sender=User)
def crear_usuario_en_tabla(sender, instance, created, **kwargs):
    """Cuando se crea un usuario en auth_user, también lo guarda en tu tabla usuarios"""
    if created:
        # Verificar si ya existe por correo
        usuario, creado = Usuario.objects.get_or_create(
            correo=instance.email,
            defaults={
                'nombres': instance.first_name,
                'apellidos': instance.last_name,
                'contrasena': instance.password,  # Guarda el hash de la contraseña
                'fecharegistro': datetime.now(),
                'suscrito': False
            }
        )
        if creado:
            print(f"✅ Usuario {instance.email} guardado en tabla usuarios")
        else:
            print(f"ℹ️ Usuario {instance.email} ya existía en tabla usuarios")

# También actualizar cuando se modifique
@receiver(post_save, sender=User)
def actualizar_usuario_en_tabla(sender, instance, **kwargs):
    """Actualiza datos en tabla usuarios cuando cambian en auth_user"""
    try:
        usuario = Usuario.objects.get(correo=instance.email)
        usuario.nombres = instance.first_name
        usuario.apellidos = instance.last_name
        usuario.save()
        print(f"✅ Usuario {instance.email} actualizado en tabla usuarios")
    except Usuario.DoesNotExist:
        # Si no existe, la señal de creación se encargará
        pass
