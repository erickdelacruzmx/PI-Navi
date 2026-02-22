import os
import django
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'NAVI.settings')
django.setup()

from django.contrib.auth.models import User
from landing.models import Usuario
from datetime import datetime

def sincronizar_usuarios():
    print("=== SINCRONIZANDO USUARIOS ===")
    usuarios_auth = User.objects.all()
    print(f"Usuarios en auth_user: {usuarios_auth.count()}")
    
    for user in usuarios_auth:
        usuario, creado = Usuario.objects.get_or_create(
            correo=user.email,
            defaults={
                'nombres': user.first_name,
                'apellidos': user.last_name,
                'contrasena': user.password,
                'fecharegistro': user.date_joined,
                'suscrito': False
            }
        )
        if creado:
            print(f"✅ Creado: {user.email}")
        else:
            print(f"ℹ️ Ya existía: {user.email}")
    
    print(f"\nTotal en tabla usuarios ahora: {Usuario.objects.count()}")

if __name__ == "__main__":
    sincronizar_usuarios()