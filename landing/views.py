from django.shortcuts import render

def home(request):  # El nombre de la función puede ser 'home' o cualquier otro
    context = {
        'ano_actual': 2026,
        'user': request.user,
    }
    return render(request, 'index.html', context)