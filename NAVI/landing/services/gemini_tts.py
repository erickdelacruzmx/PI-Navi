from dataclasses import dataclass
import base64
from io import BytesIO
import re
import wave

import requests
from django.conf import settings


VOICE_MAP = {
    'suave': 'Aoede',
}

VOICE_CANDIDATES_MAP = {
    'suave': ['Aoede', 'Puck'],
}

UNSUPPORTED_VOICE_BY_MODEL = {}

TTS_STYLE_MAP = {
    'suave': (
        'Habla con voz infantil de nino o nina de aproximadamente 10 anos, claramente aguda y juvenil. '
        'Usa tono dulce, alegre, curioso y cercano, con energia positiva y naturalidad. '
        'Mantiene una entonacion alta y ligera, sin sonar chillona ni forzada. '
        'Mantiene diccion clara, ritmo conversacional, frases cortas y expresivas. '
        'Evita sonar adulta, formal, monotona o robotica.'
    ),
}


@dataclass
class GeminiTTSAudio:
    audio_base64: str
    mime_type: str


def _build_tts_payload(clean_text, voice_name, style_instruction=None):
    payload = {
        'contents': [
            {
                'role': 'user',
                'parts': [{'text': clean_text}],
            }
        ],
        'generationConfig': {
            'responseModalities': ['AUDIO'],
            'speechConfig': {
                'voiceConfig': {
                    'prebuiltVoiceConfig': {
                        'voiceName': voice_name,
                    }
                }
            },
        },
    }

    if style_instruction:
        payload['system_instruction'] = {'parts': [{'text': style_instruction}]}

    return payload


def _extract_audio_response(data):
    candidates = data.get('candidates') or []
    if not candidates:
        raise RuntimeError('Gemini TTS no devolvio candidatos.')

    parts = ((candidates[0].get('content') or {}).get('parts') or [])
    inline_data = next((part.get('inlineData') for part in parts if part.get('inlineData')), None)
    if not inline_data:
        raise RuntimeError('Gemini TTS no devolvio audio inlineData.')

    audio_base64 = inline_data.get('data', '')
    mime_type = inline_data.get('mimeType', 'audio/wav')
    if not audio_base64:
        raise RuntimeError('Gemini TTS devolvio audio vacio.')

    normalized_mime = str(mime_type).lower()
    if normalized_mime.startswith('audio/l16'):
        audio_base64, mime_type = _convert_l16_pcm_to_wav(audio_base64, mime_type)

    return GeminiTTSAudio(audio_base64=audio_base64, mime_type=mime_type)


def _is_missing_inline_audio_error(exc):
    message = str(exc or '').lower()
    return 'inlineData'.lower() in message or 'no devolvio candidatos' in message


def _is_voice_name_bad_request(exc):
    response = getattr(exc, 'response', None)
    if response is None or response.status_code != 400:
        return False

    detail = (response.text or '').lower()
    return any(token in detail for token in ('voice', 'voicename', 'prebuiltvoiceconfig', 'speechconfig'))


def _convert_l16_pcm_to_wav(audio_base64, mime_type):
    raw_pcm = base64.b64decode(audio_base64)
    if not raw_pcm:
        raise RuntimeError('No hay audio PCM para convertir a WAV.')

    sample_rate = 24000
    channels = 1

    rate_match = re.search(r'rate=(\d+)', mime_type or '', re.IGNORECASE)
    if rate_match:
        sample_rate = int(rate_match.group(1))

    channels_match = re.search(r'channels=(\d+)', mime_type or '', re.IGNORECASE)
    if channels_match:
        channels = max(1, int(channels_match.group(1)))

    if len(raw_pcm) % 2:
        raw_pcm = raw_pcm[:-1]

    # Algunos proveedores etiquetan L16 de forma inconsistente.
    # Elegimos la interpretacion (LE/BE) con menor "ruido digital".
    endian_hint = _detect_pcm_endianness(raw_pcm)
    if endian_hint == 'be':
        pcm_le = bytearray(raw_pcm)
        for i in range(0, len(pcm_le), 2):
            pcm_le[i], pcm_le[i + 1] = pcm_le[i + 1], pcm_le[i]
    else:
        pcm_le = bytearray(raw_pcm)

    wav_buffer = BytesIO()
    with wave.open(wav_buffer, 'wb') as wav_file:
        wav_file.setnchannels(channels)
        wav_file.setsampwidth(2)
        wav_file.setframerate(sample_rate)
        wav_file.writeframes(bytes(pcm_le))

    wav_base64 = base64.b64encode(wav_buffer.getvalue()).decode('ascii')
    return wav_base64, 'audio/wav'


def _detect_pcm_endianness(raw_pcm):
    sample_count = min(len(raw_pcm) // 2, 12000)
    if sample_count < 32:
        return 'be'

    samples_le = []
    samples_be = []
    for i in range(sample_count):
        idx = i * 2
        pair = raw_pcm[idx:idx + 2]
        samples_le.append(int.from_bytes(pair, byteorder='little', signed=True))
        samples_be.append(int.from_bytes(pair, byteorder='big', signed=True))

    le_score = _pcm_noise_score(samples_le)
    be_score = _pcm_noise_score(samples_be)
    return 'le' if le_score <= be_score else 'be'


def _pcm_noise_score(samples):
    n = len(samples)
    if n < 2:
        return float('inf')

    abs_sum = 0
    diff_sum = 0
    zero_crossings = 0
    clipping = 0
    prev = samples[0]

    for i in range(n):
        current = samples[i]
        abs_sum += abs(current)
        if abs(current) >= 30000:
            clipping += 1
        if i > 0:
            diff_sum += abs(current - prev)
            if (current >= 0 > prev) or (current < 0 <= prev):
                zero_crossings += 1
        prev = current

    mean_abs = abs_sum / n
    mean_diff = diff_sum / (n - 1)
    zero_cross_ratio = zero_crossings / (n - 1)
    clipping_ratio = clipping / n

    return (mean_diff / (mean_abs + 1.0)) + (zero_cross_ratio * 0.8) + (clipping_ratio * 4.0)


def _request_tts_for_model(model, api_key, payload, timeout_seconds):
    endpoint = f'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent'
    response = requests.post(endpoint, params={'key': api_key}, json=payload, timeout=timeout_seconds)

    # Algunos modelos preview fallan con 5xx al incluir system_instruction.
    if response.status_code >= 500 and payload.get('system_instruction'):
        fallback_payload = dict(payload)
        fallback_payload.pop('system_instruction', None)
        response = requests.post(
            endpoint,
            params={'key': api_key},
            json=fallback_payload,
            timeout=timeout_seconds,
        )

    response.raise_for_status()
    data = response.json()
    try:
        return _extract_audio_response(data)
    except RuntimeError as exc:
        # Algunos modelos devuelven texto en vez de audio cuando el prompt de estilo es muy estricto.
        # Reintentamos una vez sin system_instruction antes de descartar el modelo.
        if payload.get('system_instruction') and _is_missing_inline_audio_error(exc):
            fallback_payload = dict(payload)
            fallback_payload.pop('system_instruction', None)
            retry_response = requests.post(
                endpoint,
                params={'key': api_key},
                json=fallback_payload,
                timeout=timeout_seconds,
            )
            retry_response.raise_for_status()
            return _extract_audio_response(retry_response.json())
        raise


def generate_navi_tts_audio(text, voice_profile='suave'):
    api_key = getattr(settings, 'GEMINI_API_KEY', '').strip()
    model = getattr(settings, 'GEMINI_TTS_MODEL', 'gemini-2.5-flash-preview-tts').strip()
    fallback_models = list(getattr(settings, 'GEMINI_TTS_FALLBACK_MODELS', []))
    timeout_seconds = int(getattr(settings, 'GEMINI_TTS_TIMEOUT_SECONDS', 25))
    max_chars = int(getattr(settings, 'GEMINI_TTS_MAX_CHARS', 900))

    if not api_key:
        raise RuntimeError('GEMINI_API_KEY no esta configurado.')

    clean_text = (text or '').strip()
    if not clean_text:
        raise ValueError('El texto para TTS no puede estar vacio.')
    if len(clean_text) > max_chars:
        clean_text = f"{clean_text[:max_chars].rstrip()}..."

    normalized_profile = (voice_profile or '').strip().lower()
    voice_name = VOICE_MAP.get(normalized_profile, 'Aoede')
    voice_candidates = VOICE_CANDIDATES_MAP.get(normalized_profile, [voice_name])
    if voice_name not in voice_candidates:
        voice_candidates = [voice_name, *voice_candidates]
    style_instruction = TTS_STYLE_MAP.get(normalized_profile, TTS_STYLE_MAP['suave'])

    models_to_try = []
    for model_name in [model, *fallback_models]:
        candidate = (model_name or '').strip()
        if candidate and candidate not in models_to_try:
            models_to_try.append(candidate)

    last_exception = None
    for model_name in models_to_try:
        model_unsupported_voices = UNSUPPORTED_VOICE_BY_MODEL.get(model_name, set())
        for candidate_voice in voice_candidates:
            if candidate_voice in model_unsupported_voices:
                continue

            payload = _build_tts_payload(
                clean_text=clean_text,
                voice_name=candidate_voice,
                style_instruction=style_instruction,
            )
            try:
                return _request_tts_for_model(
                    model=model_name,
                    api_key=api_key,
                    payload=payload,
                    timeout_seconds=timeout_seconds,
                )
            except requests.HTTPError as exc:
                status_code = exc.response.status_code if exc.response is not None else None
                # 400 suele indicar voiceName no soportado para este modelo; intenta siguiente voz infantil.
                if status_code == 400 and _is_voice_name_bad_request(exc):
                    UNSUPPORTED_VOICE_BY_MODEL.setdefault(model_name, set()).add(candidate_voice)
                    last_exception = exc
                    continue
                # Reintenta con siguiente modelo solo ante cuota/limite o fallos de servidor.
                if status_code in {429, 500, 502, 503, 504}:
                    last_exception = exc
                    break
                raise
            except RuntimeError as exc:
                # Si el modelo no devolvio audio inlineData/candidatos, probar siguiente modelo.
                if _is_missing_inline_audio_error(exc):
                    last_exception = exc
                    break
                raise

    if last_exception:
        raise last_exception

    raise RuntimeError('No hay modelos TTS configurados para intentar.')
