from django.contrib.auth import get_user_model
from django.core.management.base import BaseCommand

from NAVI.landing.models import Usuario
from NAVI.landing.services import sincronizar_usuario_auth


class Command(BaseCommand):
    help = "Sincroniza usuarios de auth_user hacia la tabla usuarios"

    def handle(self, *args, **options):
        user_model = get_user_model()
        usuarios_auth = user_model.objects.all()

        self.stdout.write("=== SINCRONIZANDO USUARIOS ===")
        self.stdout.write(f"Usuarios en auth_user: {usuarios_auth.count()}")

        creados = 0
        existentes = 0
        sin_email = 0

        for user in usuarios_auth:
            if not user.email:
                sin_email += 1
                self.stdout.write(self.style.WARNING("Usuario auth sin email, omitido"))
                continue

            ya_existia = Usuario.objects.filter(correo=user.email).exists()
            sincronizar_usuario_auth(user)

            if ya_existia:
                existentes += 1
                self.stdout.write(f"ℹYa existía: {user.email}")
            else:
                creados += 1
                self.stdout.write(self.style.SUCCESS(f"Creado: {user.email}"))

        self.stdout.write("")
        self.stdout.write(f"Total en tabla usuarios ahora: {Usuario.objects.count()}")
        self.stdout.write(f"Creados: {creados} | Existentes: {existentes} | Sin email: {sin_email}")
