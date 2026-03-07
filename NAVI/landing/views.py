from datetime import datetime, time
import json
import logging

from django.contrib.auth import logout
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.db import IntegrityError, transaction
from django.db.models import Sum
from django.conf import settings
from django.http import JsonResponse
from django.shortcuts import render, redirect
from django.urls import reverse
from django.utils import timezone
from django.views.decorators.http import require_GET, require_POST
from django.views.decorators.csrf import ensure_csrf_cookie

from .forms import PerfilUpdateForm
from .models import Conversation, Message, NaviVoicePreference, Usuario
from .services.gemini_client import GeminiServiceError, generate_navi_reply
from .services.gemini_tts import generate_navi_tts_audio


logger = logging.getLogger(__name__)


def _get_usuario_for_request_user(user):
    return Usuario.objects.filter(correo=user.email).first()


def _build_perfil_data(user):
    usuario = _get_usuario_for_request_user(user)
    fecha_nacimiento = None
    if usuario and usuario.fechanac:
        fecha_nacimiento = usuario.fechanac.date()

    fecha_registro = user.date_joined
    if usuario and usuario.fecharegistro:
        fecha_registro = usuario.fecharegistro

    nombres_base = (usuario.nombres if usuario and usuario.nombres else '').strip()
    if not nombres_base:
        nombres_base = (user.first_name or '').strip()
    if not nombres_base:
        nombres_base = (user.username or '').strip()

    apellidos_base = (usuario.apellidos if usuario and usuario.apellidos else '').strip()
    if not apellidos_base:
        apellidos_base = (user.last_name or '').strip()

    return {
        'nombres': nombres_base,
        'apellidos': apellidos_base,
        'correo': (user.email or (usuario.correo if usuario else '') or '').strip(),
        'fechanac': fecha_nacimiento,
        'suscrito': usuario.suscrito if usuario else False,
        'fecharegistro': fecha_registro,
    }

def home(request):  # El nombre de la función puede ser 'home' o cualquier otro
    context = {
        'ano_actual': 2026,
        'user': request.user,
    }
    return render(request, 'landing/index.html', context)


def health_view(request):
    return JsonResponse({'status': 'ok'})


def _get_or_create_active_conversation(user):
    conversation = (
        Conversation.objects.filter(user=user, is_active=True)
        .order_by('-updated_at')
        .first()
    )
    if conversation:
        return conversation
    return Conversation.objects.create(user=user, is_active=True)


def _serialize_messages(conversation, limit=30):
    recent_messages = list(
        conversation.messages.order_by('-created_at')[:limit]
    )
    recent_messages.reverse()
    return [
        {
            'id': message.id,
            'role': message.role,
            'content': message.content,
            'created_at': message.created_at.isoformat(),
        }
        for message in recent_messages
    ]


def _get_or_create_voice_preferences(user):
    preferences, _ = NaviVoicePreference.objects.get_or_create(user=user)
    return preferences


def _serialize_voice_preferences(preferences):
    return {
        'voice_output_enabled': preferences.voice_output_enabled,
        'audio_only_mode': preferences.audio_only_mode,
        'speech_rate': float(preferences.speech_rate),
        'speech_pitch': float(preferences.speech_pitch),
        'speech_lang': preferences.speech_lang,
        'voice_profile': preferences.voice_profile,
        'onboarding_completed': preferences.onboarding_completed,
    }


@login_required
@require_GET
def navi_conversation_view(request):
    conversation = _get_or_create_active_conversation(request.user)
    voice_preferences = _get_or_create_voice_preferences(request.user)
    return JsonResponse(
        {
            'conversation_id': conversation.id,
            'messages': _serialize_messages(conversation, limit=30),
            'voice_preferences': _serialize_voice_preferences(voice_preferences),
        }
    )


@login_required
@require_POST
def navi_voice_preferences_view(request):
    try:
        payload = json.loads(request.body.decode('utf-8'))
    except (json.JSONDecodeError, UnicodeDecodeError):
        return JsonResponse({'error': 'Solicitud invalida.'}, status=400)

    preferences = _get_or_create_voice_preferences(request.user)

    if 'voice_output_enabled' in payload:
        preferences.voice_output_enabled = bool(payload.get('voice_output_enabled'))

    if 'audio_only_mode' in payload:
        preferences.audio_only_mode = bool(payload.get('audio_only_mode'))

    if 'speech_lang' in payload:
        speech_lang = str(payload.get('speech_lang') or '').strip() or 'es-MX'
        preferences.speech_lang = speech_lang[:16]

    if 'voice_profile' in payload:
        voice_profile = str(payload.get('voice_profile') or '').strip().lower()
        preferences.voice_profile = 'suave'

    if 'speech_rate' in payload:
        try:
            speech_rate = float(payload.get('speech_rate'))
        except (TypeError, ValueError):
            return JsonResponse({'error': 'speech_rate invalido.'}, status=400)
        preferences.speech_rate = min(1.4, max(0.6, speech_rate))

    if 'speech_pitch' in payload:
        try:
            speech_pitch = float(payload.get('speech_pitch'))
        except (TypeError, ValueError):
            return JsonResponse({'error': 'speech_pitch invalido.'}, status=400)
        preferences.speech_pitch = min(1.4, max(0.6, speech_pitch))

    if 'onboarding_completed' in payload:
        preferences.onboarding_completed = bool(payload.get('onboarding_completed'))

    preferences.save()
    return JsonResponse({'voice_preferences': _serialize_voice_preferences(preferences)})


@login_required
@require_POST
def navi_chat_view(request):
    try:
        payload = json.loads(request.body.decode('utf-8'))
    except (json.JSONDecodeError, UnicodeDecodeError):
        return JsonResponse({'error': 'Solicitud invalida.'}, status=400)

    user_message = (payload.get('message') or '').strip()
    if not user_message:
        return JsonResponse({'error': 'Escribe un mensaje antes de enviar.'}, status=400)
    if len(user_message) > 2000:
        return JsonResponse({'error': 'El mensaje es demasiado largo.'}, status=400)

    requested_conversation_id = payload.get('conversation_id')
    if requested_conversation_id:
        conversation = Conversation.objects.filter(
            id=requested_conversation_id,
            user=request.user,
            is_active=True,
        ).first()
        if not conversation:
            return JsonResponse({'error': 'Conversacion no encontrada.'}, status=404)
    else:
        conversation = _get_or_create_active_conversation(request.user)

    now = timezone.now()
    min_seconds_between_requests = int(getattr(settings, 'NAVI_MIN_SECONDS_BETWEEN_REQUESTS', 2))

    latest_user_message = conversation.messages.filter(role=Message.ROLE_USER).order_by('-created_at').first()
    if latest_user_message:
        elapsed_seconds = (now - latest_user_message.created_at).total_seconds()
        if elapsed_seconds < min_seconds_between_requests:
            wait_seconds = max(1, int(min_seconds_between_requests - elapsed_seconds))
            return JsonResponse(
                {'error': f'Espera {wait_seconds}s antes de enviar otro mensaje.'},
                status=429,
            )

    start_of_day = now.replace(hour=0, minute=0, second=0, microsecond=0)
    daily_request_budget = int(getattr(settings, 'NAVI_DAILY_REQUEST_BUDGET', 80))
    daily_token_budget = int(getattr(settings, 'NAVI_DAILY_TOKEN_BUDGET', 14000))

    daily_request_count = Message.objects.filter(
        conversation__user=request.user,
        role=Message.ROLE_USER,
        created_at__gte=start_of_day,
    ).count()
    if daily_request_count >= daily_request_budget:
        return JsonResponse(
            {
                'error': 'Alcanzaste el limite diario de mensajes para hoy. Intenta nuevamente manana.',
            },
            status=429,
        )

    daily_usage = Message.objects.filter(
        conversation__user=request.user,
        created_at__gte=start_of_day,
    ).aggregate(
        prompt_total=Sum('prompt_tokens'),
        completion_total=Sum('completion_tokens'),
    )
    daily_tokens = int(daily_usage.get('prompt_total') or 0) + int(daily_usage.get('completion_total') or 0)
    if daily_tokens >= daily_token_budget:
        return JsonResponse(
            {
                'error': 'Hoy se alcanzo el presupuesto de uso de IA. Intenta nuevamente manana.',
            },
            status=429,
        )

    max_history_messages = int(getattr(settings, 'NAVI_MAX_HISTORY_MESSAGES', 8))
    recent_history = list(conversation.messages.order_by('-created_at')[:max_history_messages])
    recent_history.reverse()
    history_messages = [
        {'role': message.role, 'content': message.content}
        for message in recent_history
        if message.role in {Message.ROLE_USER, Message.ROLE_ASSISTANT}
    ]

    Message.objects.create(
        conversation=conversation,
        role=Message.ROLE_USER,
        content=user_message,
    )

    try:
        gemini_reply = generate_navi_reply(user_message=user_message, history_messages=history_messages)
    except GeminiServiceError as exc:
        logger.warning(
            'Gemini rechazo solicitud de chat para user_id=%s: status=%s detail=%s',
            request.user.id,
            exc.status_code,
            exc.detail,
        )
        return JsonResponse({'error': exc.user_message}, status=exc.status_code)
    except Exception as exc:
        logger.exception('Error al consultar Gemini para user_id=%s: %s', request.user.id, exc)
        return JsonResponse(
            {
                'error': 'No pude responder en este momento. Intenta de nuevo en unos segundos.',
            },
            status=502,
        )

    assistant_message = Message.objects.create(
        conversation=conversation,
        role=Message.ROLE_ASSISTANT,
        content=gemini_reply.text,
        prompt_tokens=gemini_reply.prompt_tokens,
        completion_tokens=gemini_reply.completion_tokens,
    )
    conversation.save(update_fields=['updated_at'])

    return JsonResponse(
        {
            'conversation_id': conversation.id,
            'message': {
                'id': assistant_message.id,
                'role': assistant_message.role,
                'content': assistant_message.content,
                'created_at': assistant_message.created_at.isoformat(),
            },
        }
    )


@login_required
@require_POST
def navi_tts_view(request):
    if not getattr(settings, 'GEMINI_TTS_ENABLED', True):
        return JsonResponse({'error': 'TTS de Gemini deshabilitado.'}, status=503)

    try:
        payload = json.loads(request.body.decode('utf-8'))
    except (json.JSONDecodeError, UnicodeDecodeError):
        return JsonResponse({'error': 'Solicitud invalida.'}, status=400)

    text = (payload.get('text') or '').strip()
    if not text:
        return JsonResponse({'error': 'Falta texto para sintetizar.'}, status=400)

    preferences = _get_or_create_voice_preferences(request.user)
    requested_profile = str(payload.get('voice_profile') or '').strip().lower()
    voice_profile = 'suave'

    try:
        tts_audio = generate_navi_tts_audio(text=text, voice_profile=voice_profile)
    except Exception as exc:
        logger.exception('Error TTS Gemini para user_id=%s: %s', request.user.id, exc)
        return JsonResponse({'error': 'No fue posible generar audio en este momento.'}, status=502)

    return JsonResponse(
        {
            'audio_base64': tts_audio.audio_base64,
            'mime_type': tts_audio.mime_type,
            'voice_profile': voice_profile,
            'tts_provider': 'gemini',
            'tts_model': getattr(settings, 'GEMINI_TTS_MODEL', 'unknown'),
        }
    )


@login_required
@ensure_csrf_cookie
def app_view(request):
    perfil_data = _build_perfil_data(request.user)
    perfil_form_errors = request.session.pop('perfil_form_errors', {})
    perfil_form_data = request.session.pop('perfil_form_data', {})
    perfil_form_non_field_errors = perfil_form_errors.get('non_field_errors', [])
    return render(
        request,
        'landing/app.html',
        {
            'perfil_data': perfil_data,
            'perfil_form_errors': perfil_form_errors,
            'perfil_form_non_field_errors': perfil_form_non_field_errors,
            'perfil_form_data': perfil_form_data,
        },
    )


@login_required
def perfil_view(request):
    return redirect(f"{reverse('landing:app')}?section=perfil", permanent=True)


@login_required
def configuracion_view(request):
    return redirect(f"{reverse('landing:app')}?section=configuracion", permanent=True)


@login_required
@require_POST
def perfil_update_view(request):
    form = PerfilUpdateForm(request.POST)
    if not form.is_valid():
        request.session['perfil_form_errors'] = form.errors.get_json_data(escape_html=False)
        request.session['perfil_form_data'] = {
            'nombres': request.POST.get('nombres', ''),
            'apellidos': request.POST.get('apellidos', ''),
            'fechanac': request.POST.get('fechanac', ''),
        }
        return redirect(f"{reverse('landing:app')}?section=perfil&edit=1")

    cleaned = form.cleaned_data
    user = request.user

    fecha_nac = cleaned.get('fechanac')
    fecha_nac_datetime = None
    if fecha_nac:
        naive_dt = datetime.combine(fecha_nac, time.min)
        fecha_nac_datetime = timezone.make_aware(naive_dt) if timezone.is_naive(naive_dt) else naive_dt

    try:
        with transaction.atomic():
            user.first_name = cleaned['nombres'].strip()
            user.last_name = cleaned['apellidos'].strip()
            user.save(update_fields=['first_name', 'last_name'])

            usuario = Usuario.objects.filter(correo=user.email).first()
            if usuario:
                usuario.nombres = cleaned['nombres'].strip()
                usuario.apellidos = cleaned['apellidos'].strip()
                usuario.correo = user.email
                usuario.fechanac = fecha_nac_datetime
                if not usuario.contrasena:
                    usuario.contrasena = user.password
                usuario.save()
            else:
                Usuario.objects.create(
                    nombres=cleaned['nombres'].strip(),
                    apellidos=cleaned['apellidos'].strip(),
                    correo=user.email,
                    contrasena=user.password,
                    fechanac=fecha_nac_datetime,
                    suscrito=False,
                )
    except IntegrityError:
        request.session['perfil_form_errors'] = {
            'non_field_errors': [{'message': 'No se pudo actualizar el perfil. Intenta nuevamente.'}]
        }
        request.session['perfil_form_data'] = {
            'nombres': request.POST.get('nombres', ''),
            'apellidos': request.POST.get('apellidos', ''),
            'fechanac': request.POST.get('fechanac', ''),
        }
        return redirect(f"{reverse('landing:app')}?section=perfil&edit=1")

    return redirect(f"{reverse('landing:app')}?section=perfil")


@login_required
@require_POST
def perfil_delete_view(request):
    user = request.user
    correo_usuario = (user.email or '').strip()

    with transaction.atomic():
        if correo_usuario:
            Usuario.objects.filter(correo=correo_usuario).delete()
        user.delete()

    logout(request)
    messages.success(request, 'Tu cuenta se eliminó correctamente.')
    return redirect('landing:index')


def politica_privacidad(request):
    """Renderiza la página de Política de Privacidad requerida por Meta."""
    return render(request, 'legal/politica_privacidad.html')


def eliminar_datos(request):
    """Renderiza las instrucciones para la eliminación de datos de usuario."""
    return render(request, 'legal/eliminar_datos.html')