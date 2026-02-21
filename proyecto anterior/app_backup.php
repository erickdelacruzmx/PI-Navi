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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vue 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navi-blue': '#2B308B',
                        'navi-light': '#E8EBFF',
                        'navi-lighter': '#F5F7FF',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        /* =======================================================
           CUSTOM STYLES - Complementan Tailwind
           ======================================================= */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ebff 100%);
        }

        /* Switch Toggle Tutor/Navicito */
        .mode-switch {
            display: inline-flex;
            background: white;
            border: 3px solid #2B308B;
            border-radius: 50px;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(43, 48, 139, 0.15);
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

        /* Avatar Navi Simplificado */
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

        /* Bottom Navigation - Mobile/Tablet */
        .bottom-nav {
            background: white;
            border-top: 2px solid #E8EBFF;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
        }

        .bottom-nav-item {
            transition: all 0.3s ease;
            color: #666;
        }

        .bottom-nav-item.active {
            color: #2B308B;
        }

        .bottom-nav-item:hover {
            color: #2B308B;
            transform: translateY(-2px);
        }

        /* Desktop Top Navigation */
        .desktop-topbar {
            background: white;
            border-bottom: 2px solid #E8EBFF;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .desktop-nav-item {
            transition: all 0.3s ease;
            color: #666;
            padding: 8px 20px;
            border-radius: 8px;
        }

        .desktop-nav-item.active {
            background: #2B308B;
            color: white;
        }

        .desktop-nav-item:hover {
            background: #E8EBFF;
            color: #2B308B;
        }

        .desktop-nav-item.active:hover {
            background: #2B308B;
            color: white;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #2B308B;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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

        /* =======================================================
           COMPONENTES REUTILIZABLES
           ======================================================= */
   
        /* Contenido Principal - adaptado para mobile-first */
        .desktop-main-content {
            background: white;
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        /* En mobile, el contenido ocupa todo el espacio disponible */
        @media (max-width: 767px) {
            .desktop-main-content {
                padding-bottom: 100px; /* Espacio para bottom-nav */
            }
        }

        .desktop-main-content > div {
            background: white;
            padding: 0;
        }
        
        /* Título de Sección Común */
        .section-title {
            color: #2B308B;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* =======================================================
           3. COMPONENTES (JUEGOS/BIBLIOTECA)
           ======================================================= */

        /* Grid de Categorías - Mobile First */
        .category-grid {
            display: grid;
            grid-template-columns: 1fr; /* 1 columna en móvil */
            gap: 1.5rem;
            margin-bottom: 40px;
        }

        /* Tablet y Desktop: 2 columnas */
        @media (min-width: 768px) {
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        /* Tarjeta de Categoría */
        .category-card {
            border-radius: 25px; 
            padding: 3rem 1.5rem;
            min-height: 200px; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); 
            cursor: pointer;
            display: block;
            text-align: center;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .category-card:hover {
            transform: scale(1.03); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
        }

        /* Tarjeta de Categoría Activa/Expandida */
        .category-card.active {
            grid-column: 1 / -1; /* Ocupa las dos columnas */
            min-height: 400px; 
            /* Se puede añadir un borde distintivo si es necesario */
        }

        .category-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #2b308b;
        }

        .category-description {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            margin-top: -10px;
            margin-bottom: 15px;
            padding-left: 5px;
        }

        /* Grid de Items (Actividades) dentro de la tarjeta expandida */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ddd; /* Separador del encabezado de la lista de ítems */
        }
        
        .category-card.active .item-grid {
             /* Solo se aplica el border-top cuando la tarjeta está activa */
            grid-template-columns: repeat(2, 1fr); 
        }

        /* Estilo de los Items Editables/Clickeables */
        .editable-item {
            background: #f8f9ff;
            border-radius: 8px;
            padding: 15px;
            border: 1px dashed #ccc;
            transition: all 0.3s;
            cursor: pointer;
        }

        .editable-item:hover {
            background: #eef2ff;
            border-color: #2575fc;
        }

        /* Contenedor del Título y el Ícono Principal (Left Side) */
        .item-title-group {
            display: flex;
            align-items: center;
            /* Permite que el texto se ajuste si es largo, sin empujar el ícono de favorito */
            flex-grow: 1; 
            margin-right: 10px; /* Separación del ícono de favorito */
        }

        /* Modificado para incluir el ícono de Favorito */
        .item-display {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Alinea los extremos (título y favorito) */
            width: 100%;
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
            margin-right: 8px;
        }

        .item-title {
            font-size: 16px;
            font-weight: 600;
            color: #2b308b;
            text-align: left;
            /* Asegura que el título se pueda cortar o usar puntos suspensivos si es muy largo */
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis; 
        }

        .item-content {
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #999;
        }

        .empty-icon {
            font-size: 24px;
        }
        
        /* ÍCONO DE FAVORITO (Corazón) */
        .favorite-icon-mask {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            cursor: pointer;
            background-color: #aaa; /* Gris por defecto */
            /* Icono de corazón (SVG simple para asegurar compatibilidad sin icons.css) */
           mask-image: url('icon/corazon.svg');
            mask-size: contain;
            mask-repeat: no-repeat;
            mask-position: center;
            transition: background-color 0.2s;
        }

        /* Estado Activo (Rojo) */
        .favorite-icon-mask.is-favorite {
            background-color: #d9534f; /* Rojo */
        }
        
        /* Estilos de color por categoría (Se puede mejorar centralizando esto) */
        .categoria-sonidosdelmundo {
            background-color: #ffefbe; 
        }
        /* Añadir aquí otros colores de categoría si se necesitan */

        /* =======================================================
           4.1. TOAST NOTIFICATION (Mensaje de Guardado en Pantalla)
           ======================================================= */
        .toast-notification {
            position: fixed;
            top: 20px; /* Separación de arriba */
            left: 50%;
            transform: translateX(-50%);
            background-color: #2B308B; /* Color principal */
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 1050; /* Mayor que el modal */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
            max-width: 90%;
            text-align: center;
            font-weight: 500;
        }

        .toast-notification.show {
            opacity: 1;
            visibility: visible;
            top: 30px; /* Pequeño movimiento al aparecer */
        }
        
        /* =======================================================
           4.2. MODAL (Ventana Flotante)
           ======================================================= */
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); 
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .modal-header {
            font-size: 28px;
            color: #2B308B;
            margin-bottom: 15px;
        }

        .modal-description {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.5;
        }

         .modal-button-start {
            /* Color sólido: #0057B8 */
            background: #0057B8; 
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
        }
        .modal-button-start:hover {
            /* Un color ligeramente más oscuro para el efecto hover/interacción */
            background-color: #004595;
            transform: translateY(-2px);
        }

        /* =======================================================
           5. MEDIA QUERIES - TABLET Y DESKTOP
           ======================================================= */
        
        /* TABLET: 768px - 1024px */
        @media (min-width: 768px) and (max-width: 1024px) {
            .app-container {
                max-width: 768px;
                flex-direction: row;
            }

            .mobile-header {
                display: none;
            }

            .bottom-nav {
                flex-direction: column;
                width: 100px;
                height: 100vh;
                position: fixed;
                left: 0;
                top: 0;
                padding: 20px 10px;
                border-right: 2px solid rgba(43, 48, 139, 0.1);
                border-top: none;
            }

            .nav-item {
                width: 100%;
            }

            .nav-item i {
                font-size: 28px;
            }

            .navi-avatar-container {
                margin-left: 100px;
            }

            .main-content {
                margin-left: 100px;
            }

            /* Header mini en tablet */
            .tablet-header {
                display: block;
                padding: 20px;
                text-align: center;
                margin-left: 100px;
            }

            .tablet-header .greeting {
                font-size: 24px;
            }

            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* DESKTOP: > 1024px */
        @media (min-width: 1025px) {
            .app-container {
                max-width: 1400px;
                flex-direction: row;
            }

            /* Ocultar elementos móviles */
            .mobile-header {
                display: none;
            }

            .bottom-nav {
                display: none;
            }

            /* Sidebar Desktop */
            .desktop-sidebar {
                display: flex;
                flex-direction: column;
                width: 280px;
                background: var(--light-blue);
                padding: 30px 20px;
                border-right: 2px solid rgba(43, 48, 139, 0.1);
                height: 100vh;
                position: sticky;
                top: 0;
            }

            .sidebar-header {
                text-align: center;
                margin-bottom: 30px;
            }

            .sidebar-logo {
                width: 80px;
                height: 80px;
                margin: 0 auto 15px;
            }

            .sidebar-title {
                font-size: 20px;
                font-weight: 700;
                color: var(--primary-blue);
            }

            .sidebar-greeting {
                font-size: 14px;
                color: var(--gray-text);
                margin-top: 5px;
            }

            .sidebar-nav {
                flex: 1;
            }

            .sidebar-nav-item {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 15px 20px;
                margin-bottom: 8px;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                color: var(--primary-blue);
                font-weight: 600;
                font-size: 16px;
            }

            .sidebar-nav-item:hover {
                background: rgba(43, 48, 139, 0.1);
            }

            .sidebar-nav-item.active {
                background: var(--primary-blue);
                color: white;
            }

            .sidebar-nav-item i {
                font-size: 22px;
                width: 30px;
                text-align: center;
            }

            .sidebar-footer {
                border-top: 2px solid rgba(43, 48, 139, 0.1);
                padding-top: 20px;
            }

            .sidebar-logout {
                display: flex;
                align-items: center;
                gap: 15px;
                padding: 15px 20px;
                border-radius: 12px;
                cursor: pointer;
                text-decoration: none;
                color: #d9534f;
                font-weight: 600;
                font-size: 16px;
                transition: all 0.3s ease;
            }

            .sidebar-logout:hover {
                background: rgba(217, 83, 79, 0.1);
            }

            /* Contenido principal desktop */
            .desktop-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .desktop-topbar {
                background: white;
                padding: 20px 40px;
                border-bottom: 2px solid rgba(43, 48, 139, 0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .topbar-title {
                font-size: 28px;
                font-weight: 700;
                color: var(--primary-blue);
            }

            .topbar-actions {
                display: flex;
                gap: 15px;
                align-items: center;
            }

            .topbar-link {
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                color: var(--primary-blue);
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .topbar-link:hover {
                background: rgba(43, 48, 139, 0.1);
            }

            .desktop-main-content {
                flex: 1;
                padding: 40px;
                overflow-y: auto;
            }

            /* Ajustes de grid para desktop */
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }

            .navi-avatar-container {
                padding: 60px;
            }

            .navi-avatar {
                width: 350px;
                height: 350px;
            }

            .navi-face {
                width: 350px;
                height: 350px;
            }

            .navi-eye {
                width: 35px;
                height: 35px;
            }

            .navi-mouth {
                width: 70px;
                height: 35px;
                border-width: 8px;
            }

            .navi-help-text {
                font-size: 20px;
                margin-top: 30px;
            }

            .navi-face {
                width: 250px;
                height: 250px;
            }
        }

    </style>
</head>
<body>
    <div id="app" class="app-container">
        
        <div class="toast-notification" :class="{ 'show': showToast }" v-text="toastMessage"></div>

        <!-- Sidebar Desktop -->
        <aside class="desktop-sidebar">
            <div class="sidebar-header">
                <img src="images/NAVI2.png" alt="NAVI Logo" class="sidebar-logo">
                <div class="sidebar-title">NAVI</div>
                <div class="sidebar-greeting">Hola, <?php echo htmlspecialchars($app_user_name); ?></div>
            </div>

            <div class="sidebar-nav">
                <a href="#" class="sidebar-nav-item" :class="{active: currentSection === 'juegos'}" @click="changeSection('juegos')">
                    <i class="fas fa-gamepad"></i> Juegos
                </a>
                <a href="#" class="sidebar-nav-item" :class="{active: currentSection === 'biblioteca'}" @click="changeSection('biblioteca')">
                    <i class="fas fa-book"></i> Biblioteca
                </a>
                <a href="#" class="sidebar-nav-item" :class="{active: currentSection === 'tutor'}" @click="changeSection('tutor')">
                    <i class="fas fa-user"></i> Tutor
                </a>
                <a href="#" class="sidebar-nav-item" :class="{active: currentSection === 'configuracion'}" @click="changeSection('configuracion')">
                    <i class="fas fa-cog"></i> Configuración
                </a>
            </div>

            <div class="sidebar-footer">
                <a href="index.php" class="sidebar-nav-item"><i class="fas fa-home"></i> Inicio</a>
                <a href="perfil.php" class="sidebar-nav-item"><i class="fas fa-user-circle"></i> Perfil</a>
                <a href="logout.php" class="sidebar-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </aside>

        <!-- Contenedor principal -->
        <div class="desktop-content">
            
            <!-- Topbar Desktop -->
            <div class="desktop-topbar">
                <div class="topbar-title">{{ getSectionTitle() }}</div>
                <div class="topbar-actions">
                    <span class="user-greeting">Hola, <?php echo htmlspecialchars($app_user_name); ?></span>
                </div>
            </div>

            <!-- Header Mobile -->
            <header class="mobile-header">
                <div class="greeting">Hola, <?php echo htmlspecialchars($app_user_name); ?></div>
                <div class="sub-greeting">¿En qué puedo ayudarte hoy?</div>
                
                <div class="mode-selector">
                    <button 
                        class="mode-btn" 
                        :class="{active: currentMode === 'tutor'}"
                        @click="currentMode = 'tutor'">
                        Tutor
                    </button>
                    <button 
                        class="mode-btn" 
                        :class="{active: currentMode === 'navicito'}"
                        @click="currentMode = 'navicito'">
                        Navicito
                    </button>
                </div>
            </header>

            <!-- Avatar Navi -->
            <div class="navi-avatar-container" v-if="currentMode === 'tutor'">
                <div class="navi-avatar">
                    <div class="navi-face">
                        <div class="navi-eye navi-eye-left"></div>
                        <div class="navi-eye navi-eye-right"></div>
                        <div class="navi-mouth"></div>
                    </div>
                </div>
                <p class="navi-help-text">Selecciona una opción del menú para comenzar</p>
            </div>

            <!-- Contenido Principal -->
            <main class="desktop-main-content">

            <div v-if="currentMode === 'tutor' && currentSection === 'juegos'">
                <h2 class="section-title">¡Vamos a jugar!</h2>

                <p style="font-size: 18px; color: #666; margin-top: -15px; margin-bottom: 30px;">
                    Selecciona una de las habilidades para ver y gestionar las actividades de juego disponibles.
                </p>

                <div class="category-grid" :class="{'grid-expanded': isAnyCategoryActive}">

                    <!-- INICIA CATEGORIA DE HABILIDADCOMUNICATIVA-->
                    <div class="category-card categoria-comunicativa" 
                        @click="toggleCategory('habilidadComunicativa')" 
                        :class="{'active': activeCategory === 'habilidadComunicativa'}">
                        
                        <div class="category-header">
                            <span class="category-icon-mask icon-comunicativa"></span>
                            <h3>Habilidad Comunicativa</h3>
                        </div>
                        <p class="category-description">La comunicación en niños con discapacidad visual se basa en sentidos como el tacto y el oído, y con apoyo adecuado pueden desarrollar su independencia y habilidades sociales.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'habilidadComunicativa'" style="margin-top: 15px;">
                            <div v-for="(item, index) in juegos.habilidadComunicativa" :key="index" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad está diseñada para fomentar la expresión verbal y la comprensión auditiva a través de juegos de rol y diálogos.')">
                                
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TERMINA CATEGORIA DE HABILIDADCOMUNICATIVA-->

                    <!-- INICIA CATEGORIA DE EXPLORACIONAUDITIVA-->
                    <div class="category-card categoria-auditiva" 
                        @click="toggleCategory('exploracionAuditiva')" 
                        :class="{'active': activeCategory === 'exploracionAuditiva'}">
                        
                        <div class="category-header">
                            <span class="category-icon-mask icon-auditiva"></span>
                            <h3>Exploración Auditiva</h3>
                        </div>
                        <p class="category-description">Ejercicios para reconocer, discriminar y reaccionar a distintos sonidos.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'exploracionAuditiva'" style="margin-top: 15px;">
                            <div v-for="(item, index) in juegos.exploracionAuditiva" :key="index" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad está diseñada para mejorar la identificación, discriminación y localización de fuentes sonoras.')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                      <!-- TERMINA CATEGORIA DE EXPLORACIONAUDITIVA-->
                    
                      <!-- INICIA CATEGORIA DE DESARROLLOMOTOR-->
                    <div class="category-card categoria-motor" 
                        @click="toggleCategory('desarrolloMotor')" 
                        :class="{'active': activeCategory === 'desarrolloMotor'}"> 
                        <div class="category-header">
                            <span class="category-icon-mask icon-motor"></span>
                            <h3>Desarrollo Motor</h3>
                        </div>
                        <p class="category-description">La audición es esencial para el desarrollo de personas ciegas, pero requiere entrenamiento para potenciarse, no ocurre de forma automática.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'desarrolloMotor'" style="margin-top: 15px;">
                            <div v-for="(item, index) in juegos.desarrolloMotor" :key="index" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad busca mejorar la orientación espacial y el control del cuerpo a través del movimiento.')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TERMINA CATEGORIA DE DESARROLLOMOTOR-->

                    <!-- INICIA CATEGORIA DE HABILIDADEMOCIONAL-->
                    <div class="category-card categoria-socioemocional" 
                        @click="toggleCategory('habilidadesSocioemocionales')" 
                        :class="{'active': activeCategory === 'habilidadesSocioemocionales'}">
                        <div class="category-header">
                            <span class="category-icon-mask icon-socioemocional"></span>
                            <h3>Habilidades Socioemocionales</h3>
                        </div>
                        <p class="category-description">Recursos para identificar emociones, practicar la empatía y fomentar la interacción social.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'habilidadesSocioemocionales'" style="margin-top: 15px;">
                            <div v-for="(item, index) in juegos.habilidadesSocioemocionales" :key="index" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad promueve el reconocimiento de emociones a través del tono de voz y el contacto físico (si aplica).')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- TERMINA CATEGORIA DE HABILIDADEMOCIONAL-->

            <div v-if="currentMode === 'tutor' && currentSection === 'biblioteca'">
                <h2 class="section-title">¡Vamos a escuchar!</h2>

                <p style="font-size: 18px; color: #666; margin-top: -15px; margin-bottom: 30px;">
                    Selecciona una de las habilidades para ver y gestionar las actividades de juego disponibles.
                </p>

                <div class="category-grid" :class="{'grid-expanded': isAnyCategoryActive}">
                    
                    <div class="category-card categoria-canciones" 
                        @click="toggleCategory('canciones')" 
                        :class="{'active': activeCategory === 'canciones'}">
                        <div class="category-header">
                            <span class="category-icon-mask icon-canciones"></span>
                            <h3>Canciones</h3>
                        </div>
                        <p class="category-description">Esta sección ofrece una selección de canciones diseñadas para estimular el desarrollo integral del niño a través de la música, el ritmo y el lenguaje auditivo.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'canciones'" style="margin-top: 15px;">
                            <div v-for="item in biblioteca.canciones" :key="item.id" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad está diseñada para fomentar la expresión verbal y la comprensión auditiva a través de juegos de rol y diálogos.')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                        <span class="favorite-icon-mask" 
                                            :class="{'is-favorite': item.isFavorite}"
                                            @click.stop="toggleFavorite(item)">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="category-card categoria-cuentos" 
                        @click="toggleCategory('cuentos')" 
                        :class="{'active': activeCategory === 'cuentos'}">
                        <div class="category-header">
                            <span class="category-icon-mask icon-cuentos"></span>
                            <h3>Cuentos</h3>
                        </div>
                        <p class="category-description">En esta sección encontrarás cuentos narrados con voz clara, música suave y sonidos especiales, pensados para que el niño escuche, imagine y aprenda.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'cuentos'" style="margin-top: 15px;">
                            <div v-for="item in biblioteca.cuentos" :key="item.id" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad está diseñada para mejorar la identificación, discriminación y localización de fuentes sonoras.')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                        <span class="favorite-icon-mask" 
                                            :class="{'is-favorite': item.isFavorite}"
                                            @click.stop="toggleFavorite(item)">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="category-card categoria-sonidosdelmundo" 
                        @click="toggleCategory('sonidosdelmundo')" 
                        :class="{'active': activeCategory === 'sonidosdelmundo'}"> 
                        <div class="category-header">
                            <span class="category-icon-mask icon-auditiva"></span>
                            <h3>Sonidos del Mundo</h3>
                        </div>
                        <p class="category-description">En esta sección, el niño puede escuchar y reconocer sonidos reales de su entorno y del planeta: animales, medios de transporte, objetos cotidianos y sonidos de la naturaleza.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'sonidosdelmundo'" style="margin-top: 15px;">
                            <div v-for="item in biblioteca.sonidosdelmundo" :key="item.id" 
                                class="editable-item game-button" 
                                @click.stop="openGameModal(item.content, 'Esta actividad busca mejorar la orientación espacial y el control del cuerpo a través del movimiento.')">
                                <div v-if="!item.editing" class="item-content" :class="{'has-content': item.content}">
                                    <div v-if="!item.content" class="empty-state">
                                        <span class="empty-icon">+</span>
                                    </div>
                                    <div v-else class="item-display">
                                        <div class="item-title-group">
                                            <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                            <span class="item-title">{{ item.content }}</span>
                                        </div>
                                        <span class="favorite-icon-mask" 
                                            :class="{'is-favorite': item.isFavorite}"
                                            @click.stop="toggleFavorite(item)">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="category-card categoria-favoritos" 
                        @click="toggleCategory('favoritos')" 
                        :class="{'active': activeCategory === 'favoritos'}">
                        <div class="category-header">
                            <span class="category-icon-mask icon-favoritos"></span> 
                            <h3>Favoritos</h3>
                        </div>
                        <p class="category-description">Esta sección permite guardar y acceder rápidamente a los contenidos que más le gustan al niño, como juegos, cuentos o canciones, para que pueda repetirlos cuando lo desee.</p>
                        
                        <div class="item-grid" v-if="activeCategory === 'favoritos'" style="margin-top: 15px;">
                            <div v-if="biblioteca.favoritos.length > 0">
                                <div v-for="item in biblioteca.favoritos" :key="item.id" 
                                    class="editable-item game-button" 
                                    @click.stop="openGameModal(item.content, `Contenido de ${item.category} marcado como favorito.`)">
                                    <div class="item-content has-content">
                                        <div class="item-display">
                                            <div class="item-title-group">
                                                <span class="item-icon-mask" :class="getIconClass(item)"></span>
                                                <span class="item-title">{{ item.content }}</span>
                                            </div>
                                            <span class="favorite-icon-mask is-favorite" 
                                                @click.stop="toggleFavorite(item)">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else style="grid-column: 1 / -1;">
                                <p class="empty-state">Aún no tienes elementos favoritos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-if="currentMode === 'tutor' && currentSection === 'tutor'">
                <h2 class="section-title">Tutor</h2>
                </div>

            <div v-if="currentMode === 'tutor' && currentSection === 'configuracion'">
                <h2 class="section-title">Configuración</h2>
                </div>

            <div v-if="showModal" class="modal-overlay" @click.self="closeGameModal">
                <div class="modal-content">
                    <h2 class="modal-header">¿Listo para comenzar?</h2>
                    <p class="modal-description">
                        {{ gameDescription }}
                    </p>
                    <button class="modal-button-start" @click="closeGameModal">
                        Comenzar {{ selectedGame }}
                    </button>
                </div>
            </div>

            </main>

            <!-- Navegación inferior móvil -->
            <nav class="bottom-nav">
                <a href="#" class="nav-item" :class="{active: currentSection === 'juegos'}" @click="changeSection('juegos')">
                    <i class="fas fa-gamepad"></i>
                    <span>Juegos</span>
                </a>
                <a href="#" class="nav-item" :class="{active: currentSection === 'biblioteca'}" @click="changeSection('biblioteca')">
                    <i class="fas fa-book"></i>
                    <span>Biblioteca</span>
                </a>
                <a href="#" class="nav-item" :class="{active: currentSection === 'tutor'}" @click="changeSection('tutor')">
                    <i class="fas fa-user"></i>
                    <span>Tutor</span>
                </a>
                <a href="#" class="nav-item" :class="{active: currentSection === 'configuracion'}" @click="changeSection('configuracion')">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </nav>

        </div><!-- cierre desktop-content -->
    </div><!-- cierre app-container -->


    <script>
    const { createApp, computed } = Vue

    // Arreglos de contenido inicial para la biblioteca
    const initialSongs = ["Uno, dos, tres", "ABC", "Cabeza, hombros, rodillas y pies", "A la Víbora de la Mar", "El Trenecito", "Si estás Feliz y lo sabes", "La Canción del Enojo", "Saco una Manita"];
    const initialStories = ["Los Tres Cerditos", "La Gallinita Roja", "La Liebre y la Tortuga", "El León y el Ratón"];
    const initialSounds = ["Animales", "Transporte", "Objetos Cotidianos", "Naturaleza"];
    
    createApp({
        data() {
            return {
                currentMode: 'tutor',
                currentSection: 'juegos',
                activeCategory: null, // Categoría de juego/biblioteca actualmente expandida
                
                // Propiedades para el Toast (Nueva sección)
                showToast: false,
                toastMessage: '',
                
                // Propiedades para el Modal
                showModal: false,
                selectedGame: '',
                gameDescription: '',

                // Estructura de datos para la sección Juegos (sin cambios)
                juegos: {
                    habilidadComunicativa: Array(8).fill().map(() => ({ content: '', editing: false, originalContent: '' })),
                    exploracionAuditiva: Array(6).fill().map(() => ({ content: '', editing: false, originalContent: '' })),
                    desarrolloMotor: Array(4).fill().map(() => ({ content: '', editing: false, originalContent: '' })),
                    habilidadesSocioemocionales: Array(3).fill().map(() => ({ content: '', editing: false, originalContent: '' }))
                },
                // Estructura de datos para la sección Biblioteca (favoritos es dinámico)
                biblioteca: {
                    canciones: [],
                    cuentos: [],
                    sonidosdelmundo: [],
                    favoritos: [] // Se llena dinámicamente con los ítems favoritos
                },
            }
        },
        
        computed: {
            // Propiedad computada para determinar si alguna categoría está activa
            isAnyCategoryActive() {
                return this.activeCategory !== null;
            }
        },
        
        methods: {
            // Maneja el cambio de sección del menú principal
            changeSection(section) {
                this.currentSection = section;
                this.activeCategory = null; // Cierra la categoría expandida al cambiar de sección
            },
            
            // Obtiene el título de la sección actual (para desktop topbar)
            getSectionTitle() {
                const titles = {
                    'juegos': '¡Vamos a jugar!',
                    'biblioteca': 'Biblioteca',
                    'tutor': 'Tutor Virtual',
                    'configuracion': 'Configuración'
                };
                return titles[this.currentSection] || 'NAVI';
            },
            
            // Simula ir a la página de inicio (modo 'tutor')
            goToHome() {
                this.currentMode = 'tutor';
                this.currentSection = 'juegos'; // Lo envía por defecto a juegos
                this.activeCategory = null;
            },

            // Abre o cierra la tarjeta de categoría para mostrar los items
            toggleCategory(categoryKey) {
                this.activeCategory = this.activeCategory === categoryKey ? null : categoryKey;
                this.closeAllEditing(); 
            },

            // Abre el modal de inicio de juego/actividad
            openGameModal(gameTitle, description) {
                // Previene abrir el modal si el item no tiene contenido
                if (!gameTitle) return; 
                this.selectedGame = gameTitle;
                this.gameDescription = description;
                this.showModal = true;
            },

            // Cierra el modal
            closeGameModal() {
                this.showModal = false;
                this.selectedGame = '';
                this.gameDescription = '';
            },

            /**
             * MODIFICADA: Agrega o quita un item de la lista de favoritos y muestra un Toast/Snackbar en pantalla.
             * @param {Object} item - El objeto de actividad que se va a marcar/desmarcar.
             */
            toggleFavorite(item) {
                // 1. Invertir el estado de favorito del ítem original
                item.isFavorite = !item.isFavorite;

                let message = '';

                // 2. Sincronizar la lista dinámica de favoritos y preparar el mensaje
                if (item.isFavorite) {
                    // Si se marca como favorito, agregarlo a la lista de favoritos
                    this.biblioteca.favoritos.push(item);
                    message = `"${item.content}" ha sido guardado a tus Favoritos.`;
                } else {
                    // Si se desmarca, encontrarlo y removerlo
                    const index = this.biblioteca.favoritos.findIndex(fav => fav.id === item.id);
                    if (index !== -1) {
                        this.biblioteca.favoritos.splice(index, 1);
                    }
                    message = `"${item.content}" ha sido eliminado de tus Favoritos.`;
                }
                
                // 3. Mostrar el mensaje como un Toast/Snackbar
                this.toastMessage = message;
                this.showToast = true;

                // 4. Ocultar el toast después de 3 segundos (3000 ms)
                setTimeout(() => {
                    this.showToast = false;
                    this.toastMessage = '';
                }, 3000);
            },

            // Asigna la clase de ícono (mask) según el contenido del item
            getIconClass(item) {
                // Normaliza a minúsculas y elimina acentos para facilitar coincidencias
                const normalize = (str) => (str || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
                const content = normalize(item.content);
                
                // ... (La lógica de iconos se mantiene igual para evitar código redundante)
                // --- ICONOS DE JUEGOS ---
                //ICONOS DE HABILIDAD COMUNICATIVA
        if (content.includes('sonoros')) return 'icon-sonido';
    if (content.includes('tactil')) return 'icon-tactil';
        if (content.includes('navi')) return 'icon-robot';
        if (content.includes('rimas')) return 'icon-ritmo';
        if (content.includes('dictado')) return 'icon-escritura';
        if (content.includes('lectura')) return 'icon-libro';
        if (content.includes('karaoke')) return 'icon-karaoke';
        if (content.includes('preguntas')) return 'icon-preguntas';
        
        //ICONOS EXPLORACION AUDITIVA
        if (content.includes('ambiental')) return 'icon-ambiental';
        if (content.includes('secuencia')) return 'icon-secuencia';
        if (content.includes('asociacion')) return 'icon-asociacion';
        if (content.includes('espacial')) return 'icon-espacial';
        if (content.includes('persecusion')) return 'icon-persecusion';
        if (content.includes('instrumentos')) return 'icon-instrumentos';

        //ICONOS DE DESARRILLO MOTOR
        if (content.includes('seguimiento')) return 'icon-seguimiento';
        if (content.includes('cubos')) return 'icon-cubos';
        if (content.includes('bloques')) return 'icon-bloques';
        if (content.includes('descubriendo')) return 'icon-descubriendo';

           //ICONOS DE HABILIDADES SOCIOEMOCIONALES
        if (content.includes('diario')) return 'icon-diario';
        if (content.includes('emocional')) return 'icon-emocional';
        if (content.includes('equipo')) return 'icon-equipo';

           //ICONOS DE CANCIONES
        if (content.includes('unodos')) return 'icon-unodos';
        if (content.includes('abc')) return 'icon-abc';
        if (content.includes('cabeza')) return 'icon-cabeza';
        if (content.includes('vibora')) return 'icon-vibora';
        if (content.includes('trenecito')) return 'icon-trenecito';
        if (content.includes('feliz')) return 'icon-feliz';
        if (content.includes('enojo')) return 'icon-enojo';
        if (content.includes('manita')) return 'icon-manita';

          //ICONOS DE CUENTOS
        if (content.includes('cerditos')) return 'icon-cerditos';
        if (content.includes('gallinita')) return 'icon-gallinita';
        if (content.includes('liebre')) return 'icon-liebre';
        if (content.includes('raton')) return 'icon-raton';
      

        //ICONOS DE SONIDOSDELMUNDO
        if (content.includes('animales')) return 'icon-animales';
        if (content.includes('transporte')) return 'icon-transporte';
        if (content.includes('objetos')) return 'icon-objetos';
        if (content.includes('natural')) return 'icon-natural';
                
                // Ícono por defecto si no coincide
                return 'icon-default';
            },

            // Lógica de edición (no implementada completamente en el HTML, pero mantenida)
            startEditing(item) {
                this.closeAllEditing()
                item.editing = true
                item.originalContent = item.content
            },
            saveEditing(item) {
                item.editing = false
            },
            cancelEditing(item) {
                item.editing = false
                item.content = item.originalContent
            },
            closeAllEditing() {
                // Iterar sobre todas las secciones y categorías para cerrar cualquier edición
                const datasets = [this.juegos, this.biblioteca]
                
                datasets.forEach(dataset => {
                    Object.keys(dataset).forEach(key => {
                        dataset[key].forEach(item => {
                            if (item.editing) {
                                this.cancelEditing(item)
                            }
                        })
                    })
                })
            },

            /**
             * Función auxiliar para inicializar ítems de la biblioteca con todas las propiedades necesarias.
             */
            initializeLibraryData(contentArray, categoryKey) {
                return contentArray.map((content, index) => ({
                    id: `${categoryKey}-${index}`, // ID único para el manejo en Favoritos
                    content: content,
                    category: categoryKey,
                    editing: false,
                    originalContent: content,
                    isFavorite: false // Nuevo: Estado de favorito
                }));
            }
        },  
        
        // Carga de datos inicial (simula una base de datos)
        mounted() {
            // --- JUEGOS (Se mantiene la carga inicial anterior) ---
            this.juegos.habilidadComunicativa[0].content = "Juegos Sonoros"
            this.juegos.habilidadComunicativa[1].content = "Exploración Táctil Sonora"
            this.juegos.habilidadComunicativa[2].content = "Conversación con Navi"
            this.juegos.habilidadComunicativa[3].content = "Rimas Auditivas"
            this.juegos.habilidadComunicativa[4].content = "Dictado Rítmico"
            this.juegos.habilidadComunicativa[5].content = "Lectura Auditiva"
            this.juegos.habilidadComunicativa[6].content = "Karaoke"
            this.juegos.habilidadComunicativa[7].content = "Preguntas y Respuestas"

            this.juegos.exploracionAuditiva[0].content = "Sonidos Ambientales"
            this.juegos.exploracionAuditiva[1].content = "Secuencias Sonoras"
            this.juegos.exploracionAuditiva[2].content = "Asociación Sonido-Emoción"
            this.juegos.exploracionAuditiva[3].content = "Sonido Espacial"
            this.juegos.exploracionAuditiva[4].content = "Ritmo y Persecusión"
            this.juegos.exploracionAuditiva[5].content = "Instrumentos Musicales"

            this.juegos.desarrolloMotor[0].content = "Seguimiento Sonoro"
            this.juegos.desarrolloMotor[1].content = "Cubos y Prismas"
            this.juegos.desarrolloMotor[2].content = "Bloques Braille"
            this.juegos.desarrolloMotor[3].content = "Descubriendo el Medio"
            
            this.juegos.habilidadesSocioemocionales[0].content = "Diario Personal"
            this.juegos.habilidadesSocioemocionales[1].content = "Navi Emocional"
            this.juegos.habilidadesSocioemocionales[2].content = "Actividades de Equipo"

            // --- BIBLIOTECA (Nueva carga inicial con ID y isFavorite) ---
            this.biblioteca.canciones = this.initializeLibraryData(initialSongs, 'canciones');
            this.biblioteca.cuentos = this.initializeLibraryData(initialStories, 'cuentos');
            this.biblioteca.sonidosdelmundo = this.initializeLibraryData(initialSounds, 'sonidosdelmundo');
        }
    }).mount('#app')
    </script>
</body>
</html>