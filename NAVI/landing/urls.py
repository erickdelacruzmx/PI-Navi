from django.urls import path
from . import views

app_name = 'landing'

urlpatterns = [
    path('', views.home, name='index'),  # ¡DEBE SER 'index'!
    path('app/', views.app_view, name='app'),
    path('perfil/', views.perfil_view, name='perfil'),
    path('perfil/actualizar/', views.perfil_update_view, name='perfil_actualizar'),
    path('perfil/eliminar/', views.perfil_delete_view, name='perfil_eliminar'),
    path('configuracion/', views.configuracion_view, name='configuracion'),
]