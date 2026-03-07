from django.urls import path
from . import views

app_name = 'landing'

urlpatterns = [
    path('', views.home, name='index'),  # ¡DEBE SER 'index'!
    path('health/', views.health_view, name='health'),
    path('api/csrf/', views.csrf_bootstrap_view, name='csrf_bootstrap'),
    path('api/navi/conversation/', views.navi_conversation_view, name='navi_conversation'),
    path('api/navi/chat/', views.navi_chat_view, name='navi_chat'),
    path('api/navi/preferences/', views.navi_voice_preferences_view, name='navi_preferences'),
    path('api/navi/tts/', views.navi_tts_view, name='navi_tts'),
    path('app/', views.app_view, name='app'),
    path('perfil/', views.perfil_view, name='perfil'),
    path('perfil/actualizar/', views.perfil_update_view, name='perfil_actualizar'),
    path('perfil/eliminar/', views.perfil_delete_view, name='perfil_eliminar'),
    path('configuracion/', views.configuracion_view, name='configuracion'),
    path('politica-privacidad/', views.politica_privacidad, name='politica_privacidad'),
    path('eliminar-datos/', views.eliminar_datos, name='eliminar_datos'),
]