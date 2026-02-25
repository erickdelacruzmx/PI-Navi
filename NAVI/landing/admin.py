from django.contrib import admin
from .models import Usuario, Categoria, Navicito, Actividad, Metricas, Historial, Favorito

@admin.register(Usuario)
class UsuarioAdmin(admin.ModelAdmin):
    list_display = ('idusuario', 'nombres', 'apellidos', 'correo', 'fecharegistro')
    search_fields = ('nombres', 'apellidos', 'correo')
    list_filter = ('suscrito',)

@admin.register(Categoria)
class CategoriaAdmin(admin.ModelAdmin):
    list_display = ('idcategoria', 'nombre', 'descripcion')
    search_fields = ('nombre',)

@admin.register(Navicito)
class NavicitoAdmin(admin.ModelAdmin):
    list_display = ('idnavicito', 'nombres', 'apellidos', 'tutor', 'genero')
    search_fields = ('nombres', 'apellidos')
    list_filter = ('genero',)

@admin.register(Actividad)
class ActividadAdmin(admin.ModelAdmin):
    list_display = ('idactividad', 'titulo', 'categoria', 'tipo', 'dificultad')
    search_fields = ('titulo',)
    list_filter = ('tipo', 'dificultad')

@admin.register(Metricas)
class MetricasAdmin(admin.ModelAdmin):
    list_display = ('idmetrica', 'navicito', 'pensamientologico', 'lenguajecomunicacion', 'atencion')

@admin.register(Historial)
class HistorialAdmin(admin.ModelAdmin):
    list_display = ('idhistorial', 'navicito', 'actividad', 'completado', 'fecharealizacion')
    list_filter = ('completado',)

@admin.register(Favorito)
class FavoritoAdmin(admin.ModelAdmin):
    list_display = ('idfavoritos', 'navicito', 'actividad', 'fechaagregado')