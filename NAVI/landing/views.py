from datetime import datetime, time

from django.contrib.auth import logout
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.db import IntegrityError, transaction
from django.shortcuts import render, redirect
from django.urls import reverse
from django.utils import timezone
from django.views.decorators.http import require_POST

from .forms import PerfilUpdateForm
from .models import Usuario


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


@login_required
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