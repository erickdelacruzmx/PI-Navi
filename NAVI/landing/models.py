from django.db import models
from django.conf import settings

class Genero(models.TextChoices):
    MASCULINO = 'Masculino', 'Masculino'
    FEMENINO = 'Femenino', 'Femenino'
    OTRO = 'Otro', 'Otro'

class TipoActividad(models.TextChoices):
    JUEGO = 'Juego', 'Juego'
    LECTURA = 'Lectura', 'Lectura'
    VIDEO = 'Video', 'Video'
    EJERCICIO = 'Ejercicio', 'Ejercicio'

class Dificultad(models.TextChoices):
    BAJA = 'Baja', 'Baja'
    MEDIA = 'Media', 'Media'
    ALTA = 'Alta', 'Alta'

class Usuario(models.Model):
    idusuario = models.AutoField(db_column='idusuario', primary_key=True)
    nombres = models.CharField(max_length=40, db_column='nombres')
    apellidos = models.CharField(max_length=40, db_column='apellidos')
    correo = models.CharField(max_length=255, unique=True, db_column='correo')
    contrasena = models.CharField(max_length=255, db_column='contrasena')
    fechanac = models.DateTimeField(null=True, blank=True, db_column='fechanac')
    suscrito = models.BooleanField(default=False, db_column='suscrito')
    fecharegistro = models.DateTimeField(auto_now_add=True, db_column='fecharegistro')

    class Meta:
        db_table = 'usuarios'

    def __str__(self):
        return f"{self.nombres} {self.apellidos}"

class Categoria(models.Model):
    idcategoria = models.AutoField(db_column='idcategoria', primary_key=True)
    nombre = models.CharField(max_length=30, db_column='nombre')
    descripcion = models.CharField(max_length=100, blank=True, null=True, db_column='descripcion')
    icono = models.TextField(blank=True, null=True, db_column='icono')

    class Meta:
        db_table = 'categorias'

    def __str__(self):
        return self.nombre

class Navicito(models.Model):
    idnavicito = models.AutoField(db_column='idnavicito', primary_key=True)
    tutor = models.ForeignKey('Usuario', on_delete=models.CASCADE, db_column='tutorid')
    nombres = models.CharField(max_length=40, db_column='nombres')
    apellidos = models.CharField(max_length=40, db_column='apellidos')
    fechanac = models.DateField(null=True, blank=True, db_column='fechanac')
    genero = models.CharField(max_length=10, choices=Genero.choices, blank=True, null=True, db_column='genero')
    avatar = models.TextField(blank=True, null=True, db_column='avatar')

    class Meta:
        db_table = 'navicitos'

    def __str__(self):
        return f"{self.nombres} {self.apellidos}"

class Actividad(models.Model):
    idactividad = models.AutoField(db_column='idactividad', primary_key=True)
    categoria = models.ForeignKey('Categoria', on_delete=models.SET_NULL, null=True, db_column='idcategoria')
    titulo = models.CharField(max_length=40, db_column='titulo')
    tipo = models.CharField(max_length=10, choices=TipoActividad.choices, blank=True, null=True, db_column='tipo')
    recursourl = models.CharField(max_length=100, blank=True, null=True, db_column='recursourl')
    puntuacion = models.IntegerField(blank=True, null=True, db_column='puntuacion')
    dificultad = models.CharField(max_length=10, choices=Dificultad.choices, blank=True, null=True, db_column='dificultad')

    class Meta:
        db_table = 'actividades'

    def __str__(self):
        return self.titulo

class Metricas(models.Model):
    idmetrica = models.AutoField(db_column='idmetrica', primary_key=True)
    navicito = models.OneToOneField('Navicito', on_delete=models.CASCADE, db_column='idnavicito')
    pensamientologico = models.IntegerField(default=0, db_column='pensamientologico')
    lenguajecomunicacion = models.IntegerField(default=0, db_column='lenguajecomunicacion')
    atencion = models.IntegerField(default=0, db_column='atencion')
    ultimaactualizacion = models.DateTimeField(auto_now=True, db_column='ultimaactualizacion')

    class Meta:
        db_table = 'metricas'

    def __str__(self):
        return f"Métricas de {self.navicito}"

class Historial(models.Model):
    idhistorial = models.AutoField(db_column='idhistorial', primary_key=True)
    navicito = models.ForeignKey('Navicito', on_delete=models.CASCADE, db_column='idnavicito')
    actividad = models.ForeignKey('Actividad', on_delete=models.CASCADE, db_column='idactividad')
    puntuacionobtenida = models.IntegerField(blank=True, null=True, db_column='puntuacionobtenida')
    duracionsegundos = models.IntegerField(blank=True, null=True, db_column='duracionsegundos')
    completado = models.BooleanField(default=False, db_column='completado')
    fecharealizacion = models.DateTimeField(auto_now_add=True, db_column='fecharealizacion')

    class Meta:
        db_table = 'historial'

    def __str__(self):
        return f"{self.navicito} - {self.actividad}"

class Favorito(models.Model):
    idfavoritos = models.AutoField(db_column='idfavoritos', primary_key=True)
    navicito = models.ForeignKey('Navicito', on_delete=models.CASCADE, db_column='idnavicito')
    actividad = models.ForeignKey('Actividad', on_delete=models.CASCADE, db_column='idactividad')
    fechaagregado = models.DateTimeField(auto_now_add=True, db_column='fechaagregado')

    class Meta:
        db_table = 'favoritos'
        unique_together = ['navicito', 'actividad']

    def __str__(self):
        return f"{self.navicito} - {self.actividad}"


class Conversation(models.Model):
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='navi_conversations',
    )
    title = models.CharField(max_length=120, blank=True)
    is_active = models.BooleanField(default=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'navi_conversations'
        ordering = ['-updated_at']

    def __str__(self):
        return f"Conversation {self.pk} - {self.user_id}"


class Message(models.Model):
    ROLE_USER = 'user'
    ROLE_ASSISTANT = 'assistant'
    ROLE_SYSTEM = 'system'
    ROLE_CHOICES = [
        (ROLE_USER, 'User'),
        (ROLE_ASSISTANT, 'Assistant'),
        (ROLE_SYSTEM, 'System'),
    ]

    conversation = models.ForeignKey(
        Conversation,
        on_delete=models.CASCADE,
        related_name='messages',
    )
    role = models.CharField(max_length=20, choices=ROLE_CHOICES)
    content = models.TextField()
    prompt_tokens = models.PositiveIntegerField(default=0)
    completion_tokens = models.PositiveIntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'navi_messages'
        ordering = ['created_at']

    def __str__(self):
        return f"{self.role} message {self.pk}"


class NaviVoicePreference(models.Model):
    user = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='navi_voice_preference',
    )
    voice_output_enabled = models.BooleanField(default=True)
    audio_only_mode = models.BooleanField(default=False)
    speech_rate = models.FloatField(default=0.95)
    speech_pitch = models.FloatField(default=1.0)
    speech_lang = models.CharField(max_length=16, default='es-MX')
    voice_profile = models.CharField(max_length=20, default='suave')
    onboarding_completed = models.BooleanField(default=False)
    updated_at = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'navi_voice_preferences'

    def __str__(self):
        return f"VoicePreference {self.user_id}"