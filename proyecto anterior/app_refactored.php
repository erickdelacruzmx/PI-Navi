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
        }

        .mode-btn.active {
            background: #2B308B;
            color: white;
            box-shadow: 0 2px 8px rgba(43, 48, 139, 0.3);
        }

        /* Avatar Navi - Solo círculo */
        .navi-circle {
            width: 200px;
            height: 200px;
            border: 8px solid #2B308B;
            border-radius: 50%;
            background: white;
            box-shadow: 0 10px 40px rgba(43, 48, 139, 0.2);
            transition: transform 0.3s ease;
        }

        .navi-circle:hover {
            transform: scale(1.05);
        }

        /* Desktop: más grande */
        @media (min-width: 1024px) {
            .navi-circle {
                width: 300px;
                height: 300px;
            }
        }

        /* Navegación Desktop - Items */
        .desktop-nav-link {
            transition: all 0.3s ease;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            color: #666;
            text-decoration: none;
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

        /* Tarjetas de Categoría */
        .category-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-card:hover {
            transform: translateY(-4px);
        }

        .category-card.active {
            grid-column: 1 / -1;
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
        <nav v-if="currentMode === 'tutor'" class="hidden lg:flex items-center justify-between px-8 py-4 bg-white shadow-md sticky top-0 z-50">
            <!-- Logo y Links -->
            <div class="flex items-center gap-8">
                <img src="images/NAVI2.png" alt="NAVI" class="w-14 h-14">
                <div class="flex gap-2">
                    <a href="#" @click.prevent="changeSection('juegos')" 
                       class="desktop-nav-link" :class="{active: currentSection === 'juegos'}">
                        <i class="fas fa-gamepad mr-2"></i>Juegos
                    </a>
                    <a href="#" @click.prevent="changeSection('biblioteca')" 
                       class="desktop-nav-link" :class="{active: currentSection === 'biblioteca'}">
                        <i class="fas fa-book mr-2"></i>Biblioteca
                    </a>
                    <a href="#" @click.prevent="changeSection('tutor')" 
                       class="desktop-nav-link" :class="{active: currentSection === 'tutor'}">
                        <i class="fas fa-user mr-2"></i>Tutor
                    </a>
                    <a href="#" @click.prevent="changeSection('configuracion')" 
                       class="desktop-nav-link" :class="{active: currentSection === 'configuracion'}">
                        <i class="fas fa-cog mr-2"></i>Configuración
                    </a>
                </div>
            </div>

            <!-- Switch Tutor/Navicito y User Actions -->
            <div class="flex items-center gap-6">
                <div class="mode-switch">
                    <button class="mode-btn" :class="{active: currentMode === 'tutor'}" @click="currentMode = 'tutor'">
                        Tutor
                    </button>
                    <button class="mode-btn" :class="{active: currentMode === 'navicito'}" @click="currentMode = 'navicito'">
                        Navicito
                    </button>
                </div>
                
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Hola, <strong><?php echo htmlspecialchars($app_user_name); ?></strong></span>
                    <a href="index.php" class="text-navi-blue hover:underline text-sm"><i class="fas fa-home mr-1"></i>Inicio</a>
                    <a href="perfil.php" class="text-navi-blue hover:underline text-sm"><i class="fas fa-user-circle mr-1"></i>Perfil</a>
                    <a href="logout.php" class="text-red-500 hover:underline text-sm"><i class="fas fa-sign-out-alt mr-1"></i>Salir</a>
                </div>
            </div>
        </nav>

        <!-- ============================================
             MOBILE/TABLET HEADER
             ============================================ -->
        <header class="lg:hidden bg-white shadow-md p-6 text-center">
            <h1 class="text-3xl font-bold text-navi-blue mb-2">Hola, <?php echo htmlspecialchars($app_user_name); ?></h1>
            <p class="text-gray-600 mb-6">¿En qué puedo ayudarte hoy?</p>
            
            <!-- Switch Tutor/Navicito - Siempre visible en mobile/tablet -->
            <div class="mode-switch">
                <button class="mode-btn" :class="{active: currentMode === 'tutor'}" @click="currentMode = 'tutor'">
                    Tutor
                </button>
                <button class="mode-btn" :class="{active: currentMode === 'navicito'}" @click="currentMode = 'navicito'">
                    Navicito
                </button>
            </div>
        </header>

        <!-- ============================================
             MAIN CONTENT AREA
             ============================================ -->
        <main class="container mx-auto px-4 py-8 lg:pb-8 pb-24">
            
            <!-- Avatar Navi (mostrado cuando no hay sección activa o en modo navicito) -->
            <div v-if="currentMode === 'navicito' || currentSection === 'tutor'" class="flex flex-col items-center justify-center min-h-[60vh]">
                <div class="navi-circle mb-6"></div>
                <p class="text-gray-600 text-center text-lg max-w-md">
                    {{ currentMode === 'navicito' ? 'Modo Navicito activado. El menú está oculto.' : 'Selecciona una opción del menú para comenzar' }}
                </p>
            </div>

            <!-- SECCIÓN: JUEGOS -->
            <div v-if="currentMode === 'tutor' && currentSection === 'juegos'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-10">
                <h2 class="text-4xl font-bold text-navi-blue mb-4">¡Vamos a jugar!</h2>
                <p class="text-gray-600 text-lg mb-8">
                    Selecciona una de las habilidades para ver y gestionar las actividades de juego disponibles.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- CATEGORÍA: Habilidad Comunicativa -->
                    <div class="category-card rounded-3xl p-8 bg-blue-50 shadow-lg" 
                         :class="{'active': activeCategory === 'habilidadComunicativa'}"
                         @click="toggleCategory('habilidadComunicativa')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-comunicativa w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Habilidad Comunicativa</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            La comunicación en niños con discapacidad visual se basa en sentidos como el tacto y el oído.
                        </p>
                        
                        <div v-if="activeCategory === 'habilidadComunicativa'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in juegos.habilidadComunicativa" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue"
                                 @click.stop="openGameModal(item.content, 'Actividad para fomentar la expresión verbal.')">
                                <div v-if="item.content" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-400 text-3xl">+</div>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Exploración Auditiva -->
                    <div class="category-card rounded-3xl p-8 bg-green-50 shadow-lg" 
                         :class="{'active': activeCategory === 'exploracionAuditiva'}"
                         @click="toggleCategory('exploracionAuditiva')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-auditiva w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Exploración Auditiva</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Ejercicios para reconocer, discriminar y reaccionar a distintos sonidos.
                        </p>
                        
                        <div v-if="activeCategory === 'exploracionAuditiva'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in juegos.exploracionAuditiva" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue"
                                 @click.stop="openGameModal(item.content, 'Actividad para mejorar la identificación sonora.')">
                                <div v-if="item.content" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-400 text-3xl">+</div>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Desarrollo Motor -->
                    <div class="category-card rounded-3xl p-8 bg-yellow-50 shadow-lg" 
                         :class="{'active': activeCategory === 'desarrolloMotor'}"
                         @click="toggleCategory('desarrolloMotor')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-motor w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Desarrollo Motor</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            La audición es esencial para el desarrollo de personas ciegas, pero requiere entrenamiento.
                        </p>
                        
                        <div v-if="activeCategory === 'desarrolloMotor'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in juegos.desarrolloMotor" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue"
                                 @click.stop="openGameModal(item.content, 'Actividad para mejorar la orientación espacial.')">
                                <div v-if="item.content" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-400 text-3xl">+</div>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORÍA: Habilidades Socioemocionales -->
                    <div class="category-card rounded-3xl p-8 bg-purple-50 shadow-lg" 
                         :class="{'active': activeCategory === 'habilidadesSocioemocionales'}"
                         @click="toggleCategory('habilidadesSocioemocionales')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-socioemocional w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Habilidades Socioemocionales</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            Actividades para desarrollar empatía, autoconocimiento y habilidades sociales.
                        </p>
                        
                        <div v-if="activeCategory === 'habilidadesSocioemocionales'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in juegos.habilidadesSocioemocionales" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue"
                                 @click.stop="openGameModal(item.content, 'Actividad para desarrollar inteligencia emocional.')">
                                <div v-if="item.content" class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-400 text-3xl">+</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN: BIBLIOTECA -->
            <div v-if="currentMode === 'tutor' && currentSection === 'biblioteca'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-10">
                <h2 class="text-4xl font-bold text-navi-blue mb-4">Biblioteca</h2>
                <p class="text-gray-600 text-lg mb-8">
                    Explora nuestra colección de recursos educativos.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- CANCIONES -->
                    <div class="category-card rounded-3xl p-8 bg-pink-50 shadow-lg" 
                         :class="{'active': activeCategory === 'canciones'}"
                         @click="toggleCategory('canciones')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-canciones w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Canciones</h3>
                        </div>
                        
                        <div v-if="activeCategory === 'canciones'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in biblioteca.canciones" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                    <span class="favorite-icon-mask" 
                                          :class="{'is-favorite': item.isFavorite}"
                                          @click.stop="toggleFavorite(item)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CUENTOS -->
                    <div class="category-card rounded-3xl p-8 bg-orange-50 shadow-lg" 
                         :class="{'active': activeCategory === 'cuentos'}"
                         @click="toggleCategory('cuentos')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-cuentos w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Cuentos</h3>
                        </div>
                        
                        <div v-if="activeCategory === 'cuentos'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in biblioteca.cuentos" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                    <span class="favorite-icon-mask" 
                                          :class="{'is-favorite': item.isFavorite}"
                                          @click.stop="toggleFavorite(item)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SONIDOS DEL MUNDO -->
                    <div class="category-card rounded-3xl p-8 bg-teal-50 shadow-lg" 
                         :class="{'active': activeCategory === 'sonidosdelmundo'}"
                         @click="toggleCategory('sonidosdelmundo')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <span class="category-icon-mask icon-sonidos w-10 h-10 bg-navi-blue"></span>
                            <h3 class="text-2xl font-bold text-navi-blue">Sonidos del Mundo</h3>
                        </div>
                        
                        <div v-if="activeCategory === 'sonidosdelmundo'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-for="(item, index) in biblioteca.sonidosdelmundo" :key="index" 
                                 class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                        <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                    </div>
                                    <span class="favorite-icon-mask" 
                                          :class="{'is-favorite': item.isFavorite}"
                                          @click.stop="toggleFavorite(item)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAVORITOS -->
                    <div class="category-card rounded-3xl p-8 bg-red-50 shadow-lg" 
                         :class="{'active': activeCategory === 'favoritos'}"
                         @click="toggleCategory('favoritos')">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <i class="fas fa-heart text-4xl text-red-500"></i>
                            <h3 class="text-2xl font-bold text-navi-blue">Favoritos</h3>
                        </div>
                        
                        <div v-if="activeCategory === 'favoritos'" class="mt-6 pt-6 border-t-2 border-dashed border-gray-300">
                            <div v-if="biblioteca.favoritos.length === 0" class="text-center text-gray-400 py-8">
                                No tienes favoritos aún. Marca algunos elementos como favoritos en otras categorías.
                            </div>
                            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div v-for="(item, index) in biblioteca.favoritos" :key="index" 
                                     class="editable-item bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-navi-blue">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="font-semibold text-navi-blue">{{ item.content }}</span>
                                        </div>
                                        <span class="favorite-icon-mask is-favorite"
                                              @click.stop="toggleFavorite(item)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN: CONFIGURACIÓN -->
            <div v-if="currentMode === 'tutor' && currentSection === 'configuracion'" class="bg-white rounded-2xl shadow-lg p-6 lg:p-10">
                <h2 class="text-4xl font-bold text-navi-blue mb-4">Configuración</h2>
                <p class="text-gray-600 text-lg mb-8">
                    Ajusta las preferencias de la aplicación.
                </p>
                <div class="text-center text-gray-500 py-12">
                    <i class="fas fa-cog text-6xl mb-4"></i>
                    <p>Panel de configuración en desarrollo...</p>
                </div>
            </div>

        </main>

        <!-- ============================================
             BOTTOM NAVIGATION - MOBILE/TABLET (< 1024px)
             ============================================ -->
        <nav v-if="currentMode === 'tutor'" class="lg:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t-2 border-navi-light z-50">
            <div class="flex justify-around items-center py-3">
                <a href="#" @click.prevent="changeSection('juegos')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-4 py-2 rounded-lg"
                   :class="{active: currentSection === 'juegos'}">
                    <i class="fas fa-gamepad text-2xl"></i>
                    <span class="text-xs font-semibold">Juegos</span>
                </a>
                <a href="#" @click.prevent="changeSection('biblioteca')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-4 py-2 rounded-lg"
                   :class="{active: currentSection === 'biblioteca'}">
                    <i class="fas fa-book text-2xl"></i>
                    <span class="text-xs font-semibold">Biblioteca</span>
                </a>
                <a href="#" @click.prevent="changeSection('tutor')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-4 py-2 rounded-lg"
                   :class="{active: currentSection === 'tutor'}">
                    <i class="fas fa-user text-2xl"></i>
                    <span class="text-xs font-semibold">Tutor</span>
                </a>
                <a href="#" @click.prevent="changeSection('configuracion')" 
                   class="bottom-nav-link flex flex-col items-center gap-1 px-4 py-2 rounded-lg"
                   :class="{active: currentSection === 'configuracion'}">
                    <i class="fas fa-cog text-2xl"></i>
                    <span class="text-xs font-semibold">Config</span>
                </a>
            </div>
        </nav>

        <!-- ============================================
             MODAL DE JUEGO
             ============================================ -->
        <div v-if="showModal" @click="closeGameModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div @click.stop class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <h3 class="text-3xl font-bold text-navi-blue mb-4">{{ selectedGame }}</h3>
                <p class="text-gray-600 mb-6 leading-relaxed">{{ gameDescription }}</p>
                <button @click="closeGameModal" 
                        class="w-full bg-navi-blue text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                    Comenzar {{ selectedGame }}
                </button>
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
                currentSection: 'juegos',
                activeCategory: null,
                showModal: false,
                selectedGame: '',
                gameDescription: '',
                showToast: false,
                toastMessage: '',
                
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
            changeSection(section) {
                this.currentSection = section;
                this.activeCategory = null;
            },
            
            toggleCategory(categoryKey) {
                this.activeCategory = this.activeCategory === categoryKey ? null : categoryKey;
            },
            
            openGameModal(gameTitle, description) {
                if (!gameTitle) return;
                this.selectedGame = gameTitle;
                this.gameDescription = description;
                this.showModal = true;
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
                
                // Iconos de juegos
                if (content.includes('sonoros')) return 'icon-sonido';
                if (content.includes('tactil')) return 'icon-tactil';
                if (content.includes('navi')) return 'icon-robot';
                if (content.includes('rimas')) return 'icon-ritmo';
                if (content.includes('dictado')) return 'icon-escritura';
                if (content.includes('lectura')) return 'icon-libro';
                if (content.includes('karaoke')) return 'icon-karaoke';
                if (content.includes('preguntas')) return 'icon-preguntas';
                if (content.includes('ambiental')) return 'icon-ambiental';
                if (content.includes('secuencia')) return 'icon-secuencia';
                if (content.includes('asociacion')) return 'icon-asociacion';
                if (content.includes('espacial')) return 'icon-espacial';
                if (content.includes('persecusion')) return 'icon-persecusion';
                if (content.includes('instrumentos')) return 'icon-instrumentos';
                if (content.includes('seguimiento')) return 'icon-seguimiento';
                if (content.includes('cubos')) return 'icon-cubos';
                if (content.includes('bloques')) return 'icon-bloques';
                if (content.includes('descubriendo')) return 'icon-descubriendo';
                if (content.includes('diario')) return 'icon-diario';
                if (content.includes('emocional')) return 'icon-emocional';
                if (content.includes('equipo')) return 'icon-equipo';
                
                // Iconos de biblioteca
                if (content.includes('unodos') || content.includes('uno')) return 'icon-unodos';
                if (content.includes('abc')) return 'icon-abc';
                if (content.includes('cabeza')) return 'icon-cabeza';
                if (content.includes('vibora')) return 'icon-vibora';
                if (content.includes('trenecito')) return 'icon-trenecito';
                if (content.includes('feliz')) return 'icon-feliz';
                if (content.includes('enojo')) return 'icon-enojo';
                if (content.includes('manita')) return 'icon-manita';
                if (content.includes('cerditos')) return 'icon-cerditos';
                if (content.includes('gallinita')) return 'icon-gallinita';
                if (content.includes('liebre')) return 'icon-liebre';
                if (content.includes('raton')) return 'icon-raton';
                if (content.includes('animales')) return 'icon-animales';
                if (content.includes('transporte')) return 'icon-transporte';
                if (content.includes('objetos')) return 'icon-objetos';
                if (content.includes('natural')) return 'icon-natural';
                
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
