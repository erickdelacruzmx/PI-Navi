<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_email = $_SESSION['user_email'] ?? 'usuario@ejemplo.com';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | NAVI</title>
    <link rel="icon" href="icon/Navi.svg" type="image/svg+xml">
    <link rel="stylesheet" href="icons.css">

    <!-- Tailwind CSS + Config de colores como en app.php -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navi-blue': '#2B308B',
                        'navi-light': '#E8EBFF',
                        'navi-lighter': '#F5F7FF',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Navegación Desktop - Items (igual que app.php) */
        .desktop-nav-link {
            transition: all 0.3s ease;
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 600;
            color: #666;
            text-decoration: none;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
        }
        @media (min-width: 1280px) {
            .desktop-nav-link {
                padding: 14px 32px;
                border-radius: 12px;
                min-height: 56px;
            }
        }
        .desktop-nav-link:hover {
            background: #E8EBFF;
            color: #2B308B;
        }
        .desktop-nav-link.active {
            background: #2B308B;
            color: white !important;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-gray-50 to-white" style="font-family: Poppins, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;">
    <!-- Top bar (match app.php) -->
    <nav class="hidden lg:flex items-center justify-between px-8 xl:px-12 py-5 xl:py-6 bg-white shadow-md sticky top-0 z-50">
        <div class="flex items-center gap-6 xl:gap-8">
            <a href="app.php" class="group">
                <div class="w-12 xl:w-16 h-12 xl:h-16 rounded-full bg-white shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                    <img src="images/NAVI2.png" alt="NAVI" class="w-10 xl:w-14 h-10 xl:h-14 object-contain">
                </div>
            </a>
            <div class="flex gap-3 xl:gap-4">
                <a href="app.php" class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl"><i class="fas fa-home mr-2 xl:mr-3 2xl:mr-4"></i>Inicio</a>
                <a href="app.php" class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl"><i class="fas fa-gamepad mr-2 xl:mr-3 2xl:mr-4"></i>Juegos</a>
                <a href="app.php" class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl"><i class="fas fa-book mr-2 xl:mr-3 2xl:mr-4"></i>Biblioteca</a>
                <a href="app.php" class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl"><i class="fas fa-cog mr-2 xl:mr-3 2xl:mr-4"></i>Configuración</a>
                <a href="perfil.php" class="desktop-nav-link active text-base xl:text-xl 2xl:text-2xl"><i class="fas fa-user mr-2 xl:mr-3 2xl:mr-4"></i>Perfil</a>
            </div>
        </div>
        <div class="flex items-center gap-4 xl:gap-5 2xl:gap-6">
            <span class="text-sm xl:text-base 2xl:text-lg text-gray-600">Hola, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
            <a href="logout.php" class="text-red-500 hover:underline text-sm xl:text-base 2xl:text-lg"><i class="fas fa-sign-out-alt mr-1"></i>Salir</a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8 max-w-[95%] lg:max-w-[1200px]">
        <!-- Cabecera de perfil -->
        <section class="bg-white rounded-2xl shadow-lg p-6 lg:p-10 mb-8">
            <div class="flex flex-col lg:flex-row items-center lg:items-start gap-6 lg:gap-10 border-b border-gray-200 pb-6 lg:pb-8 mb-6 lg:mb-8">
                <div class="w-28 h-28 rounded-full bg-navi-light flex items-center justify-center text-navi-blue text-4xl shadow"><i class="fas fa-user"></i></div>
                <div class="text-center lg:text-left">
                    <h1 class="text-2xl lg:text-3xl font-bold text-navi-blue mb-2"><?php echo htmlspecialchars($user_name); ?></h1>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($user_email); ?> • Miembro desde Enero 2024</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-navi-blue font-bold text-xl">12</div>
                            <div class="text-gray-500 text-sm">Proyectos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-navi-blue font-bold text-xl">5</div>
                            <div class="text-gray-500 text-sm">Colaboradores</div>
                        </div>
                        <div class="text-center">
                            <div class="text-navi-blue font-bold text-xl">98%</div>
                            <div class="text-gray-500 text-sm">Actividad</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="#" class="bg-navi-blue text-white px-5 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors"><i class="fas fa-edit mr-2"></i>Editar Perfil</a>
                <a href="#" class="bg-gray-100 text-gray-800 px-5 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-colors"><i class="fas fa-share-alt mr-2"></i>Compartir</a>
                <a href="logout.php" class="bg-red-500 text-white px-5 py-3 rounded-xl font-semibold hover:bg-red-600 transition-colors"><i class="fas fa-sign-out-alt mr-2"></i>Salir</a>
            </div>
        </section>

        <!-- Preferencias -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow p-6 lg:p-8">
                <h2 class="text-xl lg:text-2xl font-bold text-navi-blue mb-4"><i class="fas fa-palette mr-2"></i>Personalización</h2>
                <p class="text-gray-500 mb-6">Personaliza tu experiencia en la plataforma</p>
                <div class="space-y-4">
                    <label class="flex items-center gap-3"><input type="radio" name="theme" class="w-5 h-5" checked> <span>Modo claro</span></label>
                    <label class="flex items-center gap-3"><input type="radio" name="theme" class="w-5 h-5"> <span>Modo oscuro</span></label>
                    <label class="flex items-center gap-3"><input type="radio" name="theme" class="w-5 h-5"> <span>Automático (según sistema)</span></label>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 lg:p-8">
                <h2 class="text-xl lg:text-2xl font-bold text-navi-blue mb-4"><i class="fas fa-language mr-2"></i>Idioma</h2>
                <p class="text-gray-500 mb-6">Selecciona tu idioma preferido</p>
                <select class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-navi-blue">
                    <option>Español</option>
                    <option>English</option>
                    <option>Français</option>
                    <option>Português</option>
                </select>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 lg:p-8">
                <h2 class="text-xl lg:text-2xl font-bold text-navi-blue mb-4"><i class="fas fa-volume-up mr-2"></i>Volumen</h2>
                <p class="text-gray-500 mb-6">Ajusta los niveles de audio del sistema</p>
                <div class="space-y-6">
                    <div>
                        <div class="font-semibold mb-2">Volumen principal</div>
                        <input type="range" min="0" max="100" value="75" class="w-full accent-navi-blue">
                    </div>
                    <div>
                        <div class="font-semibold mb-2">Notificaciones</div>
                        <input type="range" min="0" max="100" value="60" class="w-full accent-navi-blue">
                    </div>
                    <div>
                        <div class="font-semibold mb-2">Sonidos del sistema</div>
                        <input type="range" min="0" max="100" value="40" class="w-full accent-navi-blue">
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow p-6 lg:p-8">
                <h2 class="text-xl lg:text-2xl font-bold text-navi-blue mb-4"><i class="fas fa-shield-alt mr-2"></i>Cuenta</h2>
                <p class="text-gray-500 mb-6">Acciones rápidas de cuenta</p>
                <div class="flex flex-wrap gap-3">
                    <a href="configuracion.php" class="px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 transition">Ajustes</a>
                    <a href="index.php" class="px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 transition">Inicio</a>
                </div>
            </div>
        </section>

        <footer class="text-center text-gray-500 text-sm mt-10">&copy; 2025 NAVI. Todos los derechos reservados.</footer>
    </main>
</body>
</html>