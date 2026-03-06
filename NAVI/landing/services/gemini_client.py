from dataclasses import dataclass
from typing import Optional
import re

import requests
from django.conf import settings


SYSTEM_INSTRUCTION = (
    "Eres Navi, una asistente educativa para familias y tutores de ninos. "
    "Responde en espanol neutro, con tono claro, empatico y practico. "
    "Tus respuestas deben ser optimizadas para escucharse en voz alta: frases cortas, sin tablas ni markdown. "
    "Cuando no tengas informacion suficiente, pide una aclaracion breve. "
    "Prioriza recomendaciones accionables para actividades de aprendizaje. "
    "Cuando ayudes a un menor con discapacidad visual, describe pasos de forma auditiva y concreta. "
    "Evita respuestas peligrosas o contenido no apto para menores. "
    "No inicies cada respuesta con saludos ni con la frase 'Hola, soy Navi'. "
    "Presentate solo si la persona pregunta quien eres."
)


@dataclass
class GeminiReply:
    text: str
    prompt_tokens: int = 0
    completion_tokens: int = 0


@dataclass
class GeminiServiceError(Exception):
    status_code: int
    user_message: str
    detail: str = ''

    def __str__(self):
        return self.detail or self.user_message


def _build_contents(history_messages, user_message):
    max_chars = int(getattr(settings, 'NAVI_MAX_HISTORY_CHARS_PER_MESSAGE', 420))

    def _clip(value):
        text = (value or '').strip()
        if len(text) <= max_chars:
            return text
        return f"{text[:max_chars].rstrip()}..."

    contents = []
    for message in history_messages:
        role = message.get('role')
        content = _clip(message.get('content'))
        if not content:
            continue

        if role == 'assistant':
            gemini_role = 'model'
        elif role == 'user':
            gemini_role = 'user'
        else:
            continue

        contents.append(
            {
                'role': gemini_role,
                'parts': [{'text': content}],
            }
        )

    contents.append({'role': 'user', 'parts': [{'text': user_message}]})
    return contents


def generate_navi_reply(user_message, history_messages):
    api_key = getattr(settings, 'GEMINI_API_KEY', '').strip()
    model = getattr(settings, 'GEMINI_MODEL', 'gemini-1.5-flash').strip()
    timeout_seconds = int(getattr(settings, 'GEMINI_TIMEOUT_SECONDS', 20))
    max_output_tokens = int(getattr(settings, 'GEMINI_MAX_OUTPUT_TOKENS', 220))
    temperature = float(getattr(settings, 'GEMINI_TEMPERATURE', 0.45))

    if not api_key:
        raise RuntimeError('GEMINI_API_KEY no esta configurado.')

    if not user_message or not user_message.strip():
        raise ValueError('El mensaje del usuario no puede estar vacio.')

    payload = {
        'system_instruction': {
            'parts': [
                {
                    'text': SYSTEM_INSTRUCTION,
                }
            ]
        },
        'contents': _build_contents(history_messages, user_message.strip()),
        'generationConfig': {
            'temperature': max(0.1, min(1.0, temperature)),
            'maxOutputTokens': max(80, min(1024, max_output_tokens)),
        },
    }

    endpoint = f'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent'
    try:
        response = requests.post(
            endpoint,
            params={'key': api_key},
            json=payload,
            timeout=timeout_seconds,
        )
    except requests.Timeout as exc:
        raise GeminiServiceError(
            status_code=504,
            user_message='Gemini esta tardando mas de lo esperado. Intenta de nuevo en unos segundos.',
            detail=str(exc),
        ) from exc
    except requests.RequestException as exc:
        raise GeminiServiceError(
            status_code=503,
            user_message='No fue posible conectar con Gemini en este momento.',
            detail=str(exc),
        ) from exc

    if not response.ok:
        detail = _extract_api_error_detail(response)
        status_code, user_message = _map_error_to_user_message(response.status_code, detail)
        raise GeminiServiceError(status_code=status_code, user_message=user_message, detail=detail)

    data = response.json()
    candidates = data.get('candidates') or []
    if not candidates:
        raise RuntimeError('Gemini no devolvio candidatos de respuesta.')

    parts = ((candidates[0].get('content') or {}).get('parts') or [])
    text_parts = [part.get('text', '') for part in parts if part.get('text')]
    reply_text = '\n'.join(text_parts).strip()
    if not reply_text:
        raise RuntimeError('Gemini devolvio una respuesta vacia.')

    reply_text = _normalize_reply_text(reply_text, history_messages)

    usage = data.get('usageMetadata') or {}
    return GeminiReply(
        text=reply_text,
        prompt_tokens=int(usage.get('promptTokenCount') or 0),
        completion_tokens=int(usage.get('candidatesTokenCount') or 0),
    )


def _normalize_reply_text(reply_text: str, history_messages) -> str:
    text = (reply_text or '').strip()
    if not text:
        return text

    had_assistant_before = any(
        (message.get('role') == 'assistant' and str(message.get('content') or '').strip())
        for message in (history_messages or [])
    )
    if not had_assistant_before:
        return text

    patterns = [
        r'^(hola[\s,!.:-]*)?(soy\s+navi[\s,!.:-]*)',
        r'^(hola[\s,!.:-]*)?(te\s+habla\s+navi[\s,!.:-]*)',
        r'^(hola[\s,!.:-]*)?(navi\s+al\s+habla[\s,!.:-]*)',
    ]
    normalized = text
    for pattern in patterns:
        normalized = re.sub(pattern, '', normalized, flags=re.IGNORECASE).lstrip()

    return normalized or text


def _extract_api_error_detail(response: requests.Response) -> str:
    try:
        data = response.json()
    except ValueError:
        return (response.text or '').strip()

    error = data.get('error') if isinstance(data, dict) else None
    if isinstance(error, dict):
        message = str(error.get('message') or '').strip()
        status = str(error.get('status') or '').strip()
        if message and status:
            return f'{status}: {message}'
        if message:
            return message
    return str(data)


def _map_error_to_user_message(http_status: int, detail: str) -> tuple[int, str]:
    normalized = (detail or '').lower()

    if http_status == 400:
        return 400, 'La solicitud a Gemini fue invalida. Ajusta la configuracion del modelo.'
    if http_status == 401:
        return 401, 'La API key de Gemini no es valida.'
    if http_status == 403:
        if 'api key not valid' in normalized or 'credential is invalid' in normalized:
            return 401, 'La API key de Gemini no es valida.'
        if 'has not been used' in normalized or 'api is not enabled' in normalized:
            return 403, 'Debes habilitar Generative Language API en el proyecto de esta API key.'
        return 403, 'La API key no tiene permisos para usar Gemini en este proyecto.'
    if http_status == 404:
        return 404, 'El modelo configurado de Gemini no esta disponible para esta API key.'
    if http_status == 429:
        return 429, 'Gemini alcanzo el limite de cuota o velocidad. Intenta de nuevo en unos minutos.'
    if http_status >= 500:
        return 502, 'Gemini no esta disponible temporalmente. Intenta de nuevo en unos segundos.'
    return 502, 'No fue posible obtener respuesta de Gemini en este momento.'
