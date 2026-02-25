from allauth.account.forms import SignupForm
from django import forms

from .models import Usuario

class CustomSignupForm(SignupForm):
    first_name = forms.CharField(
        max_length=30, 
        label='Nombre',
        widget=forms.TextInput(attrs={'placeholder': 'Tu nombre'}),
        required=False
    )
    last_name = forms.CharField(
        max_length=30, 
        label='Apellido',
        widget=forms.TextInput(attrs={'placeholder': 'Tu apellido'}),
        required=False
    )

    def save(self, request):
        user = super().save(request)
        user.first_name = self.cleaned_data.get('first_name', '')
        user.last_name = self.cleaned_data.get('last_name', '')
        user.save()

        Usuario.objects.update_or_create(
            correo=user.email,
            defaults={
                'nombres': user.first_name or '',
                'apellidos': user.last_name or '',
                'contrasena': user.password,
            },
        )

        return user


class PerfilUpdateForm(forms.Form):
    nombres = forms.CharField(max_length=40, required=True)
    apellidos = forms.CharField(max_length=40, required=True)
    fechanac = forms.DateField(required=False, widget=forms.DateInput(attrs={'type': 'date'}))
