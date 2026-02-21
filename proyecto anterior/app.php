<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
$app_user_name = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAVI: Aprender Escuchando</title>
    <link rel="icon" href="icon/Navi.svg" type="image/svg+xml">
    <link rel="stylesheet" href="icons.css">
    
    <!-- Tailwind CSS CDN -->
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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vue 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ebff 100%);
        }

        /* Switch Tutor/Navicito */
        .mode-switch {
            display: inline-flex;
            background: white;
            border: 3px solid #2B308B;
            border-radius: 50px;
            padding: 4px;
            box-shadow: 0 4px 15px rgba(43, 48, 139, 0.2);
        }

        .mode-btn {
            padding: 10px 32px;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            color: #2B308B;
            min-height: 44px;
        }

        .mode-btn.active {
            background: #2B308B;
            color: white;
            box-shadow: 0 2px 8px rgba(43, 48, 139, 0.3);
        }

        /* Desktop: Switch más grande para táctil */
        @media (min-width: 1024px) {
            .mode-switch {
                border: 4px solid #2B308B;
                padding: 6px;
            }

            .mode-btn {
                padding: 16px 48px;
                font-size: 20px;
                min-height: 60px;
            }
        }

        /* Avatar Navi - Sin cara, clickeable */
        .navi-circle {
            width: 180px;
            height: 180px;
            border: 8px solid #2B308B;
            border-radius: 50%;
            background: white;
            box-shadow: 0 10px 40px rgba(43, 48, 139, 0.2);
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
        }

        .navi-circle:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 50px rgba(43, 48, 139, 0.3);
        }

        .navi-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Animación de pulsación cuando está "hablando" */
        .navi-circle.talking {
            animation: pulse-talk 1.5s ease-in-out infinite;
        }

        @keyframes pulse-talk {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 40px rgba(43, 48, 139, 0.2), 
                            0 0 0 0 rgba(43, 48, 139, 0.4);
            }
            50% {
                transform: scale(1.08);
                box-shadow: 0 15px 50px rgba(43, 48, 139, 0.3),
                            0 0 0 20px rgba(43, 48, 139, 0);
            }
        }

        /* Desktop: más grande para pantallas táctiles */
        @media (min-width: 1024px) {
            .navi-circle {
                width: 320px;
                height: 320px;
                border: 12px solid #2B308B;
            }
        }

        /* Navegación Desktop - Items */
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

        /* Bottom Nav Mobile - Items */
        .bottom-nav-link {
            transition: all 0.3s ease;
            color: #999;
        }

        .bottom-nav-link.active {
            color: #2B308B;
        }

        .bottom-nav-link:hover {
            color: #2B308B;
            transform: translateY(-2px);
        }

        /* Toast */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #2B308B;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .toast-notification.show {
            opacity: 1;
            visibility: visible;
            top: 30px;
        }

        /* Tarjetas de Categoría - Estilo del mockup */
        .category-card {
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            min-height: 180px;
        }

        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .category-card.active {
            grid-column: 1 / -1;
        }

        /* Tablet: Cards medianas */
        @media (min-width: 768px) {
            .category-card {
                min-height: 220px;
            }
        }

        /* Desktop: Cards grandes y cuadradas para pantallas táctiles */
        @media (min-width: 1024px) {
            .category-card {
                min-height: 220px;
                aspect-ratio: 1 / 1;
            }
        }

        /* Desktop XL: Cards extra grandes */
        @media (min-width: 1280px) {
            .category-card {
                min-height: 220px;
            }
        }

        /* Desktop 2XL: Cards máximas */
        @media (min-width: 1536px) {
            .category-card {
                min-height: 240px;
            }

            .category-card h3 {
                font-size: 2.25rem !important;
            }

            .category-card p {
                font-size: 1.5rem !important;
            }
        }

        /* Icono grande en tarjeta */
        .category-icon-large {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        /* Items en grid más compacto */
        .item-card-compact {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .item-card-compact:hover {
            transform: translateX(4px);
        }

        /* Items editables */
        .editable-item {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .editable-item:hover {
            transform: scale(1.02);
        }

        .item-icon-mask {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            background-color: #2b308b;
            mask-size: contain;
            mask-repeat: no-repeat;
            mask-position: center;
            -webkit-mask-size: contain;
            -webkit-mask-repeat: no-repeat;
            -webkit-mask-position: center;
        }

        .favorite-icon-mask {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            cursor: pointer;
            background-color: #aaa;
            mask-image: url('icon/corazon.svg');
            mask-size: contain;
            mask-repeat: no-repeat;
            mask-position: center;
            transition: background-color 0.2s;
        }

        .favorite-icon-mask.is-favorite {
            background-color: #d9534f;
        }
    </style>
</head>
<body class="min-h-screen">
    <div id="app">
        
        <!-- Toast Notification -->
        <div class="toast-notification" :class="{ 'show': showToast }" v-text="toastMessage"></div>

        <!-- ============================================
             DESKTOP NAVIGATION - TOP BAR (>= 1024px)
             ============================================ -->
        <nav v-if="currentMode === 'tutor'" class="hidden lg:flex items-center justify-between px-8 xl:px-12 py-5 xl:py-6 bg-white shadow-md sticky top-0 z-50">
            <!-- Logo y Links -->
            <div class="flex items-center gap-6 xl:gap-8">
                <a href="#" @click.prevent="changeSection('navi')" class="group">
                    <div class="w-12 xl:w-16 h-12 xl:h-16 rounded-full bg-white shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                        <img src="images/NAVI2.png" alt="NAVI" class="w-10 xl:w-14 h-10 xl:h-14 object-contain">
                    </div>
                </a>
                <div class="flex gap-3 xl:gap-4">
                    <a href="#" @click.prevent="changeSection('navi')" 
                       class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl" :class="{active: currentSection === 'navi'}">
                        <i class="fas fa-home mr-2 xl:mr-3 2xl:mr-4"></i>Inicio
                    </a>
                    <a href="#" @click.prevent="changeSection('juegos')" 
                       class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl" :class="{active: currentSection === 'juegos'}">
                        <i class="fas fa-gamepad mr-2 xl:mr-3 2xl:mr-4"></i>Juegos
                    </a>
                    <a href="#" @click.prevent="changeSection('biblioteca')" 
                       class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl" :class="{active: currentSection === 'biblioteca'}">
                        <i class="fas fa-book mr-2 xl:mr-3 2xl:mr-4"></i>Biblioteca
                    </a>
                    <a href="#" @click.prevent="changeSection('estadisticas')" 
                       class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl" :class="{active: currentSection === 'estadisticas'}">
                        <i class="fas fa-chart-bar mr-2 xl:mr-3 2xl:mr-4"></i>Estadísticas
                    </a>
                    <a href="#" @click.prevent="changeSection('configuracion')" 
                       class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl" :class="{active: currentSection === 'configuracion'}">
                        <i class="fas fa-cog mr-2 xl:mr-3 2xl:mr-4"></i>Configuración
                    </a>
                    <a href="perfil.php" class="desktop-nav-link text-base xl:text-xl 2xl:text-2xl">
                        <i class="fas fa-user mr-2 xl:mr-3 2xl:mr-4"></i>Perfil
                    </a>
                </div>
            </div>

            <!-- User Actions (sin switch) -->
            <div class="flex items-center gap-4 xl:gap-5 2xl:gap-6">
                <span class="text-sm xl:text-base 2xl:text-lg text-gray-600">Hola, <strong><?php echo htmlspecialchars($app_user_name); ?></strong></span>
                <a href="logout.php" class="text-red-500 hover:underline text-sm xl:text-base 2xl:text-lg"><i class="fas fa-sign-out-alt mr-1"></i>Salir</a>
            </div>
        </nav>

        <!-- ============================================
             SECCIÓN DEDICADA NAVI - Vista principal
             ============================================ -->
        <div v-if="currentSection === 'navi'" 
             class="flex flex-col items-center justify-center bg-gradient-to-b from-gray-50 to-white"
             :style="currentMode === 'tutor' ? 'min-height: calc(100vh - 180px);' : 'min-height: 100vh;'">
            
            <!-- Switch siempre visible arriba -->
            <div class="mb-8 lg:mb-12 xl:mb-16 2xl:mb-20">
                <div class="mode-switch">
                    <button class="mode-btn" :class="{active: currentMode === 'tutor'}" @click="currentMode = 'tutor'">
                        Tutor
                    </button>
                    <button class="mode-btn" :class="{active: currentMode === 'navicito'}" @click="currentMode = 'navicito'">
                        Navicito
                    </button>
                </div>
            </div>

            <!-- Avatar NAVI -->
            <div class="navi-circle mb-6 lg:mb-10 xl:mb-12 2xl:mb-16" 
                 :class="{'talking': isTalking}"
                 @click="handleNaviClick"
                 style="box-shadow: 0 20px 60px rgba(43, 48, 139, 0.15);">
            </div>
            
            <!-- Texto descriptivo -->
            <p class="text-gray-600 text-center text-base lg:text-2xl xl:text-3xl 2xl:text-4xl max-w-md lg:max-w-2xl xl:max-w-4xl 2xl:max-w-5xl px-4 lg:px-8 xl:px-10 2xl:px-12">
                {{ naviMessage }}
            </p>
        </div>

        <!-- ============================================
             MAIN CONTENT AREA
             ============================================ -->
        <main class="container mx-auto px-4 py-8 lg:pb-8 pb-24 max-w-[95%] lg:max-w-[1800px]" v-show="currentMode === 'tutor' && currentSection !== 'navi'">

            <!-- SECCIÓN: JUEGOS -->
            <div v-if="currentMode === 'tutor' && currentSection === 'juegos'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-8 xl:p-8 2xl:p-10">
                <h2 class="text-xl lg:text-2xl xl:text-2xl 2xl:text-3xl font-bold text-navi-blue mb-4 lg:mb-6 xl:mb-8 2xl:mb-10">¡Vamos a jugar!</h2>
                <p class="text-gray-600 text-xs lg:text-sm xl:text-sm 2xl:text-base mb-6 lg:mb-8 xl:mb-8 2xl:mb-10">
                    Selecciona una de las habilidades para ver y gestionar las actividades de juego disponibles.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4 lg:gap-4 xl:gap-4 2xl:gap-5">
                    
                    <!-- CATEGORÍA: Habilidad Comunicativa -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-comunicativa shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openCategoryModal('habilidadComunicativa')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-comunicativa text-purple-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Habilidad Comunicativa</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">
                                Mejora tu expresión y comprensión
                            </p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Exploración Auditiva -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-auditiva shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openCategoryModal('exploracionAuditiva')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-auditiva text-pink-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Exploración Auditiva</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">
                                Reconoce y discrimina sonidos
                            </p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Desarrollo Motor -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-motor shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openCategoryModal('desarrolloMotor')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-motor text-yellow-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Desarrollo Motor</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Mejora tu orientación espacial</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Habilidades Socioemocionales -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-socioemocional shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openCategoryModal('habilidadesSocioemocionales')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-socioemocional text-teal-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Habilidades Socioemocionales</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Desarrolla empatía y habilidades sociales</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN: BIBLIOTECA -->
            <div v-if="currentMode === 'tutor' && currentSection === 'biblioteca'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-8 xl:p-8 2xl:p-10">
                <h2 class="text-xl lg:text-2xl xl:text-2xl 2xl:text-3xl font-bold text-navi-blue mb-4 lg:mb-6 xl:mb-8 2xl:mb-10">Biblioteca</h2>
                <p class="text-gray-600 text-xs lg:text-sm xl:text-sm 2xl:text-base mb-6 lg:mb-8 xl:mb-8 2xl:mb-10">
                    Explora nuestra colección de recursos educativos.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4 lg:gap-4 xl:gap-4 2xl:gap-5">
                    
                    <!-- CANCIONES -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-canciones shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openLibraryModal('canciones')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-canciones text-purple-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Canciones</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Para cantar y aprender</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- CUENTOS -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-cuentos shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openLibraryModal('cuentos')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-cuentos text-pink-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Cuentos</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Historias para escuchar</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- SONIDOS DEL MUNDO -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-sonidos shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openLibraryModal('sonidosdelmundo')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-sonidos text-yellow-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Sonidos del Mundo</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Explora diferentes sonidos</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                    <!-- FAVORITOS -->
                    <div class="category-card rounded-2xl p-5 lg:p-5 xl:p-6 2xl:p-7 categoria-favoritos shadow-md cursor-pointer hover:shadow-xl transition-shadow flex flex-col" 
                         @click="openLibraryModal('favoritos')">
                        <div class="flex-1 flex flex-col items-center justify-center text-center">
                            <div class="category-icon-mask icon-favoritos text-teal-800 mb-4 lg:mb-5 xl:mb-5 2xl:mb-6"></div>
                            <h3 class="text-sm md:text-base lg:text-base xl:text-base 2xl:text-lg font-bold text-navi-blue mb-2 lg:mb-3 xl:mb-3 2xl:mb-4">Favoritos</h3>
                            <p class="text-gray-600 text-xs md:text-sm lg:text-sm xl:text-sm 2xl:text-sm">Tus favoritos guardados</p>
                        </div>
                        <div class="flex justify-end mt-4 lg:mt-4 xl:mt-5 2xl:mt-5">
                            <i class="fas fa-arrow-right text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN: ESTADÍSTICAS -->
            <div v-if="currentMode === 'tutor' && currentSection === 'estadisticas'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-12">
                <h2 class="text-4xl lg:text-5xl font-bold text-navi-blue mb-4">Estadísticas y Progreso</h2>
                <p class="text-gray-600 text-lg lg:text-xl mb-10">
                    Visualiza el progreso y estadísticas de aprendizaje.
                </p>
                <div class="text-center text-gray-500 py-20">
                    <i class="fas fa-chart-bar text-8xl mb-6"></i>
                    <p class="text-xl">Panel de estadísticas en desarrollo...</p>
                </div>
            </div>

            <!-- SECCIÓN: CONFIGURACIÓN -->
            <div v-if="currentMode === 'tutor' && currentSection === 'configuracion'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-12">
                <h2 class="text-4xl lg:text-5xl font-bold text-navi-blue mb-4">Configuración</h2>
                <p class="text-gray-600 text-lg lg:text-xl mb-10">
                    Ajusta las preferencias de la aplicación.
                </p>
                <div class="text-center text-gray-500 py-20">
                    <i class="fas fa-cog text-8xl mb-6"></i>
                    <p>Panel de configuración en desarrollo...</p>
                </div>
            </div>

        </main>

        <!-- ============================================
             BOTTOM NAVIGATION - MOBILE/TABLET (< 1024px)
             ============================================ -->
        <nav v-if="currentMode === 'tutor'" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t-2 border-navi-light z-50">
            <div class="flex justify-around items-center py-3 md:py-4">
                <a href="#" @click.prevent="changeSection('navi')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-3 md:px-4 py-2 rounded-lg min-w-[60px]"
                   :class="{active: currentSection === 'navi'}">
                    <i class="fas fa-home text-2xl md:text-3xl"></i>
                    <span class="text-xs md:text-sm font-semibold">Inicio</span>
                </a>
                <a href="#" @click.prevent="changeSection('juegos')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-3 md:px-4 py-2 rounded-lg min-w-[60px]"
                   :class="{active: currentSection === 'juegos'}">
                    <i class="fas fa-gamepad text-2xl md:text-3xl"></i>
                    <span class="text-xs md:text-sm font-semibold">Juegos</span>
                </a>
                <a href="#" @click.prevent="changeSection('biblioteca')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-3 md:px-4 py-2 rounded-lg min-w-[60px]"
                   :class="{active: currentSection === 'biblioteca'}">
                    <i class="fas fa-book text-2xl md:text-3xl"></i>
                    <span class="text-xs md:text-sm font-semibold">Biblioteca</span>
                </a>
                <a href="#" @click.prevent="changeSection('estadisticas')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-3 md:px-4 py-2 rounded-lg min-w-[60px]"
                   :class="{active: currentSection === 'estadisticas'}">
                    <i class="fas fa-chart-bar text-2xl md:text-3xl"></i>
                    <span class="text-xs md:text-sm font-semibold">Stats</span>
                </a>
                <a href="#" @click.prevent="changeSection('configuracion')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-3 md:px-4 py-2 rounded-lg min-w-[60px]"
                   :class="{active: currentSection === 'configuracion'}">
                    <i class="fas fa-cog text-2xl md:text-3xl"></i>
                    <span class="text-xs md:text-sm font-semibold">Config</span>
                </a>
            </div>
        </nav>

        <!-- ============================================
             MODAL DE JUEGO
             ============================================ -->
        <div v-if="showModal" @click="closeGameModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4"
             style="z-index: 9999;">
            <div @click.stop class="bg-white rounded-2xl p-8 lg:p-12 xl:p-14 2xl:p-16 max-w-md lg:max-w-2xl xl:max-w-4xl 2xl:max-w-5xl w-full shadow-2xl">
                <h3 class="text-xl lg:text-2xl xl:text-2xl 2xl:text-3xl font-bold text-navi-blue mb-4 lg:mb-6 xl:mb-8 2xl:mb-10">{{ selectedGame }}</h3>
                <p class="text-gray-600 text-base lg:text-xl xl:text-xl 2xl:text-2xl mb-6 lg:mb-10 xl:mb-12 2xl:mb-16 leading-relaxed">{{ gameDescription }}</p>
                <button @click="closeGameModal" 
                        class="w-full bg-navi-blue text-white py-4 lg:py-6 xl:py-8 2xl:py-10 px-6 lg:px-8 xl:px-10 2xl:px-12 rounded-xl text-lg lg:text-xl xl:text-2xl 2xl:text-3xl font-semibold hover:bg-blue-700 transition-all active:scale-95">
                    Comenzar {{ selectedGame }}
                </button>
            </div>
        </div>

        <!-- ============================================
             MODAL DE CATEGORÍA
             ============================================ -->
        <div v-if="showCategoryModal" @click="closeCategoryModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 overflow-y-auto"
             style="z-index: 9999;">
            <div @click.stop class="rounded-2xl p-6 lg:p-8 xl:p-8 2xl:p-10 max-w-4xl lg:max-w-7xl xl:max-w-[90rem] 2xl:max-w-[100rem] w-full shadow-2xl my-8" :class="getCategoryModalClass()">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between mb-6 lg:mb-10 xl:mb-12 2xl:mb-16">
                    <div class="flex items-center gap-4 lg:gap-6 xl:gap-8 2xl:gap-10">
                        <div class="category-icon-mask" :class="getCategoryIcon()"></div>
                        <div>
                            <h3 class="text-2xl lg:text-4xl xl:text-4xl 2xl:text-5xl font-bold text-navi-blue">{{ getCategoryTitle() }}</h3>
                            <p class="text-gray-600 text-sm lg:text-lg xl:text-xl 2xl:text-2xl">{{ getCategoryDescription() }}</p>
                        </div>
                    </div>
                    <button @click="closeCategoryModal" class="text-gray-400 hover:text-gray-600 transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <i class="fas fa-times text-2xl lg:text-4xl xl:text-4xl 2xl:text-5xl"></i>
                    </button>
                </div>

                <!-- Lista de Actividades -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 xl:gap-8 2xl:gap-10">
                    <template v-for="(item, index) in getCategoryGames()" :key="index">
                        <div v-if="item.content"
                             class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-4 lg:p-6 xl:p-8 2xl:p-10 border-2 border-gray-200 hover:border-navi-blue hover:shadow-lg transition-all cursor-pointer group min-h-[70px] md:min-h-[80px] lg:min-h-[100px] xl:min-h-[120px] 2xl:min-h-[140px] active:scale-95"
                             @click="openGameModal(item.content, getCategoryGameDescription())">
                            <div class="flex items-center gap-3 lg:gap-5 xl:gap-6 2xl:gap-8">
                                <div class="item-icon-mask" :class="getIconClass(item)"></div>
                                <span class="text-base md:text-lg lg:text-xl xl:text-2xl 2xl:text-3xl font-semibold text-navi-blue flex-1 group-hover:text-blue-700 transition-colors">{{ item.content }}</span>
                                <i class="fas fa-play-circle text-navi-blue text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl group-hover:text-blue-700 transition-colors"></i>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL DE BIBLIOTECA -->
    <div v-if="showLibraryModal" @click="closeLibraryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" style="z-index: 9999;">
        <div @click.stop class="rounded-2xl shadow-2xl max-w-4xl lg:max-w-7xl xl:max-w-[90rem] 2xl:max-w-[100rem] w-full max-h-[85vh] overflow-y-auto" :class="getLibraryModalClass()">
            <div class="sticky top-0 border-b border-gray-200 p-6 lg:p-10 xl:p-12 2xl:p-16 flex items-center justify-between" :class="getLibraryModalClass()">
                <div class="flex items-center gap-4 lg:gap-6 xl:gap-8 2xl:gap-10">
                    <div class="category-icon-mask" :class="getLibraryIcon()"></div>
                    <div>
                        <h3 class="text-2xl lg:text-4xl xl:text-4xl 2xl:text-5xl font-bold text-navi-blue">{{ getLibraryTitle() }}</h3>
                        <p class="text-gray-600 text-sm lg:text-lg xl:text-xl 2xl:text-2xl">{{ getLibraryDescription() }}</p>
                    </div>
                </div>
                <button @click="closeLibraryModal" class="text-gray-400 hover:text-gray-600 transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center">
                    <i class="fas fa-times text-2xl lg:text-4xl xl:text-4xl 2xl:text-5xl"></i>
                </button>
            </div>

            <!-- Lista de Contenido -->
            <div class="p-6 lg:p-10 xl:p-12 2xl:p-16">
                <!-- Estado vacío para favoritos -->
                <div v-if="currentLibraryCategory === 'favoritos' && getLibraryItems().length === 0" class="text-center text-gray-400 py-12 lg:py-20 xl:py-24 2xl:py-32">
                    <div class="category-icon-mask icon-favoritos mx-auto mb-3 lg:mb-6 xl:mb-8 2xl:mb-10 opacity-30"></div>
                    <p class="text-base md:text-lg lg:text-xl xl:text-2xl 2xl:text-3xl">No tienes favoritos aún.<br>Marca algunos elementos en otras categorías.</p>
                </div>

                <!-- Grid de elementos -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 xl:gap-8 2xl:gap-10">
                    <template v-for="(item, index) in getLibraryItems()" :key="index">
                        <div v-if="item.content"
                             class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-4 lg:p-6 xl:p-8 2xl:p-10 border-2 border-gray-200 hover:border-navi-blue hover:shadow-lg transition-all min-h-[70px] md:min-h-[80px] lg:min-h-[100px] xl:min-h-[120px] 2xl:min-h-[140px] active:scale-95">
                            <div class="flex items-center gap-3 lg:gap-5 xl:gap-6 2xl:gap-8">
                                <div class="item-icon-mask" :class="getIconClass(item)"></div>
                                <span class="text-base md:text-lg lg:text-xl xl:text-2xl 2xl:text-3xl font-semibold text-navi-blue flex-1">{{ item.content }}</span>
                                          <i :class="item.isFavorite ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-400'"
                                   class="cursor-pointer hover:scale-110 transition-transform text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-2xl min-w-[44px] min-h-[44px] flex items-center justify-center active:scale-95"
                                   @click="toggleFavorite(item)"></i>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    </div>

    <script>
    const { createApp } = Vue;

    // Datos iniciales
    const initialSongs = ["Uno, dos, tres", "ABC", "Cabeza, hombros, rodillas y pies", "A la Víbora de la Mar", "El Trenecito", "Si estás Feliz y lo sabes", "La Canción del Enojo", "Saco una Manita"];
    const initialStories = ["Los Tres Cerditos", "La Gallinita Roja", "La Liebre y la Tortuga", "El León y el Ratón"];
    const initialSounds = ["Animales", "Transporte", "Objetos Cotidianos", "Naturaleza"];
    
    createApp({
        data() {
            return {
                currentMode: 'tutor',
                currentSection: 'navi',
                activeCategory: null,
                showModal: false,
                showCategoryModal: false,
                showLibraryModal: false,
                currentCategory: null,
                currentLibraryCategory: null,
                selectedGame: '',
                gameDescription: '',
                showToast: false,
                toastMessage: '',
                isTalking: false,
                naviMessage: 'Hola, ¿en qué puedo ayudarte hoy?',
                
                juegos: {
                    habilidadComunicativa: Array(8).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                    exploracionAuditiva: Array(6).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                    desarrolloMotor: Array(4).fill(null).map(() => ({ content: '', editing: false, originalContent: '' })),
                    habilidadesSocioemocionales: Array(3).fill(null).map(() => ({ content: '', editing: false, originalContent: '' }))
                },
                
                biblioteca: {
                    canciones: [],
                    cuentos: [],
                    sonidosdelmundo: [],
                    favoritos: []
                }
            }
        },
        
        methods: {
            handleNaviClick() {
                // Activar animación de "hablando"
                this.isTalking = true;
                
                // Mensajes de respuesta aleatorios
                const messages = [
                    '¡Hola! Estoy aquí para ayudarte a aprender.',
                    '¿Quieres explorar alguna actividad?',
                    '¡Me encanta aprender contigo!',
                    '¿En qué te puedo ayudar hoy?',
                    '¡Vamos a divertirnos aprendiendo!',
                    'Puedes explorar juegos, canciones y mucho más.',
                    '¡Estoy listo para ayudarte!'
                ];
                
                // Cambiar mensaje aleatoriamente
                this.naviMessage = messages[Math.floor(Math.random() * messages.length)];
                
                // Desactivar animación después de 3 segundos
                setTimeout(() => {
                    this.isTalking = false;
                    this.naviMessage = 'Hola, ¿en qué puedo ayudarte hoy?';
                }, 3000);
            },
            
            changeSection(section) {
                this.currentSection = section;
                this.activeCategory = null;
            },
            
            // Métodos para modal de biblioteca
            openLibraryModal(category) {
                this.currentLibraryCategory = category;
                this.showLibraryModal = true;
            },
            
            closeLibraryModal() {
                this.showLibraryModal = false;
                this.currentLibraryCategory = null;
            },
            
            getLibraryTitle() {
                const titles = {
                    'canciones': 'Canciones',
                    'cuentos': 'Cuentos',
                    'sonidosdelmundo': 'Sonidos del Mundo',
                    'favoritos': 'Favoritos'
                };
                return titles[this.currentLibraryCategory] || '';
            },
            
            getLibraryDescription() {
                const descriptions = {
                    'canciones': 'Explora canciones interactivas para aprender y divertirte',
                    'cuentos': 'Descubre historias mágicas y educativas',
                    'sonidosdelmundo': 'Escucha y aprende sonidos del entorno',
                    'favoritos': 'Tus elementos favoritos guardados'
                };
                return descriptions[this.currentLibraryCategory] || '';
            },
            
            getLibraryIcon() {
                const icons = {
                    'canciones': 'icon-canciones',
                    'cuentos': 'icon-cuentos',
                    'sonidosdelmundo': 'icon-sonidos',
                    'favoritos': 'icon-favoritos'
                };
                return icons[this.currentLibraryCategory] || '';
            },
            
            getLibraryItems() {
                if (!this.currentLibraryCategory) return [];
                return this.biblioteca[this.currentLibraryCategory] || [];
            },
            
            toggleCategory(categoryKey) {
                this.activeCategory = this.activeCategory === categoryKey ? null : categoryKey;
            },
            
            openCategoryModal(category) {
                this.currentCategory = category;
                this.showCategoryModal = true;
            },
            
            closeCategoryModal() {
                this.showCategoryModal = false;
                this.currentCategory = null;
            },
            
            getCategoryTitle() {
                const titles = {
                    'habilidadComunicativa': 'Habilidad Comunicativa',
                    'exploracionAuditiva': 'Exploración Auditiva',
                    'desarrolloMotor': 'Desarrollo Motor',
                    'habilidadesSocioemocionales': 'Habilidades Socioemocionales'
                };
                return titles[this.currentCategory] || '';
            },
            
            getCategoryDescription() {
                const descriptions = {
                    'habilidadComunicativa': 'Mejora tu expresión y comprensión',
                    'exploracionAuditiva': 'Reconoce y discrimina sonidos',
                    'desarrolloMotor': 'Mejora tu orientación espacial',
                    'habilidadesSocioemocionales': 'Desarrolla empatía y habilidades sociales'
                };
                return descriptions[this.currentCategory] || '';
            },
            
            getCategoryIcon() {
                const icons = {
                    'habilidadComunicativa': 'icon-comunicativa text-purple-800',
                    'exploracionAuditiva': 'icon-auditiva text-pink-800',
                    'desarrolloMotor': 'icon-motor text-yellow-800',
                    'habilidadesSocioemocionales': 'icon-socioemocional text-teal-800'
                };
                return icons[this.currentCategory] || '';
            },
            
            getCategoryGames() {
                return this.juegos[this.currentCategory] || [];
            },
            
            getCategoryGameDescription() {
                const descriptions = {
                    'habilidadComunicativa': 'Actividad para fomentar la expresión verbal y comunicación efectiva.',
                    'exploracionAuditiva': 'Actividad para mejorar la identificación y discriminación sonora.',
                    'desarrolloMotor': 'Actividad para mejorar la orientación espacial y coordinación.',
                    'habilidadesSocioemocionales': 'Actividad para desarrollar inteligencia emocional y habilidades sociales.'
                };
                return descriptions[this.currentCategory] || '';
            },
            
            getCategoryModalClass() {
                const classes = {
                    'habilidadComunicativa': 'categoria-comunicativa',
                    'exploracionAuditiva': 'categoria-auditiva',
                    'desarrolloMotor': 'categoria-motor',
                    'habilidadesSocioemocionales': 'categoria-socioemocional'
                };
                return classes[this.currentCategory] || 'bg-white';
            },
            
            getLibraryModalClass() {
                const classes = {
                    'canciones': 'categoria-canciones',
                    'cuentos': 'categoria-cuentos',
                    'sonidosdelmundo': 'categoria-sonidos',
                    'favoritos': 'categoria-favoritos'
                };
                return classes[this.currentLibraryCategory] || 'bg-white';
            },
            
            openGameModal(gameTitle, description) {
                if (!gameTitle) return;
                this.selectedGame = gameTitle;
                this.gameDescription = description;
                this.showModal = true;
                this.showCategoryModal = false;
            },
            
            closeGameModal() {
                this.showModal = false;
                this.selectedGame = '';
                this.gameDescription = '';
            },
            
            toggleFavorite(item) {
                item.isFavorite = !item.isFavorite;
                
                if (item.isFavorite) {
                    this.biblioteca.favoritos.push(item);
                    this.toastMessage = `"${item.content}" ha sido guardado a tus Favoritos.`;
                } else {
                    const index = this.biblioteca.favoritos.findIndex(fav => fav.id === item.id);
                    if (index !== -1) {
                        this.biblioteca.favoritos.splice(index, 1);
                    }
                    this.toastMessage = `"${item.content}" ha sido eliminado de tus Favoritos.`;
                }
                
                this.showToast = true;
                setTimeout(() => {
                    this.showToast = false;
                }, 3000);
            },
            
            getIconClass(item) {
                const normalize = (str) => (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                const content = normalize(item.content);
                
                // Iconos de juegos - Habilidad Comunicativa
                if (content.includes('sonoros')) return 'icon-sonido';
                if (content.includes('tactil')) return 'icon-tactil';
                if (content.includes('navi') || content.includes('conversacion')) return 'icon-robot';
                if (content.includes('rimas')) return 'icon-ritmo';
                if (content.includes('dictado')) return 'icon-escritura';
                if (content.includes('lectura')) return 'icon-libro';
                if (content.includes('karaoke')) return 'icon-karaoke';
                if (content.includes('preguntas')) return 'icon-preguntas';
                
                // Iconos de juegos - Exploración Auditiva
                if (content.includes('ambiental')) return 'icon-ambiental';
                if (content.includes('secuencia')) return 'icon-secuencia';
                if (content.includes('asociacion')) return 'icon-asociacion';
                if (content.includes('espacial')) return 'icon-espacial';
                if (content.includes('persecusion') || content.includes('ritmo')) return 'icon-persecusion';
                if (content.includes('instrumentos')) return 'icon-instrumentos';
                
                // Iconos de juegos - Desarrollo Motor
                if (content.includes('seguimiento')) return 'icon-seguimiento';
                if (content.includes('cubos')) return 'icon-cubos';
                if (content.includes('bloques') || content.includes('braille')) return 'icon-bloques';
                if (content.includes('descubriendo') || content.includes('medio')) return 'icon-descubriendo';
                
                // Iconos de juegos - Habilidades Socioemocionales
                if (content.includes('diario')) return 'icon-diario';
                if (content.includes('emocional')) return 'icon-emocional';
                if (content.includes('equipo')) return 'icon-equipo';
                
                // Iconos de biblioteca - Canciones
                if (content.includes('unodos') || content.includes('uno')) return 'icon-unodos';
                if (content.includes('abc')) return 'icon-abc';
                if (content.includes('cabeza') || content.includes('hombros')) return 'icon-cabeza';
                if (content.includes('vibora')) return 'icon-vibora';
                if (content.includes('trenecito') || content.includes('tren')) return 'icon-trenecito';
                if (content.includes('feliz')) return 'icon-feliz';
                if (content.includes('enojo')) return 'icon-enojo';
                if (content.includes('manita') || content.includes('mano')) return 'icon-manita';
                
                // Iconos de biblioteca - Cuentos
                if (content.includes('cerditos') || content.includes('cerdo')) return 'icon-cerditos';
                if (content.includes('gallinita') || content.includes('gallina')) return 'icon-gallinita';
                if (content.includes('liebre') || content.includes('tortuga')) return 'icon-liebre';
                if (content.includes('leon') || content.includes('raton')) return 'icon-raton';
                
                // Iconos de biblioteca - Sonidos del Mundo
                if (content.includes('animales')) return 'icon-animales';
                if (content.includes('transporte')) return 'icon-transporte';
                if (content.includes('objetos') || content.includes('cotidiano')) return 'icon-objetos';
                if (content.includes('natural') || content.includes('naturaleza')) return 'icon-natural';
                
                // Default - usar icono de categoría según el contexto
                return 'icon-default';
            },
            
            initializeLibraryData(contentArray, categoryKey) {
                return contentArray.map((content, index) => ({
                    id: `${categoryKey}-${index}`,
                    content: content,
                    category: categoryKey,
                    editing: false,
                    originalContent: content,
                    isFavorite: false
                }));
            }
        },
        
        watch: {
            currentMode(newMode) {
                // Cuando se cambia a modo Navicito, ir a la sección NAVI
                if (newMode === 'navicito') {
                    this.currentSection = 'navi';
                }
            }
        },
        
        mounted() {
            // Inicializar juegos
            this.juegos.habilidadComunicativa[0].content = "Juegos Sonoros";
            this.juegos.habilidadComunicativa[1].content = "Exploración Táctil Sonora";
            this.juegos.habilidadComunicativa[2].content = "Conversación con Navi";
            this.juegos.habilidadComunicativa[3].content = "Rimas Auditivas";
            this.juegos.habilidadComunicativa[4].content = "Dictado Rítmico";
            this.juegos.habilidadComunicativa[5].content = "Lectura Auditiva";
            this.juegos.habilidadComunicativa[6].content = "Karaoke";
            this.juegos.habilidadComunicativa[7].content = "Preguntas y Respuestas";

            this.juegos.exploracionAuditiva[0].content = "Sonidos Ambientales";
            this.juegos.exploracionAuditiva[1].content = "Secuencias Sonoras";
            this.juegos.exploracionAuditiva[2].content = "Asociación Sonido-Emoción";
            this.juegos.exploracionAuditiva[3].content = "Sonido Espacial";
            this.juegos.exploracionAuditiva[4].content = "Ritmo y Persecusión";
            this.juegos.exploracionAuditiva[5].content = "Instrumentos Musicales";

            this.juegos.desarrolloMotor[0].content = "Seguimiento Sonoro";
            this.juegos.desarrolloMotor[1].content = "Cubos y Prismas";
            this.juegos.desarrolloMotor[2].content = "Bloques Braille";
            this.juegos.desarrolloMotor[3].content = "Descubriendo el Medio";
            
            this.juegos.habilidadesSocioemocionales[0].content = "Diario Personal";
            this.juegos.habilidadesSocioemocionales[1].content = "Navi Emocional";
            this.juegos.habilidadesSocioemocionales[2].content = "Actividades de Equipo";

            // Inicializar biblioteca
            this.biblioteca.canciones = this.initializeLibraryData(initialSongs, 'canciones');
            this.biblioteca.cuentos = this.initializeLibraryData(initialStories, 'cuentos');
            this.biblioteca.sonidosdelmundo = this.initializeLibraryData(initialSounds, 'sonidosdelmundo');
        }
    }).mount('#app');
    </script>
</body>
</html>


















































































