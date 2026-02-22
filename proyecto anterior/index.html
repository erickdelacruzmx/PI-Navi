 <?php
// Iniciar sesión al principio del archivo
session_start();

// Verificar si el usuario está logueado para mostrar opciones diferentes
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_name = $_SESSION['user_name'] ?? '';
?>

 <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navi - Plataforma Educativa para Niños Ciegos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --accent: #3b82f6;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            
            /* Colores para las características */
            --color-1: #CEDEFF;
            --color-2: #D0CEFF;
            --color-3: #FFD9FC;
            --color-4: #FFEFBE;
            --color-5: #B8E0E3;
            --color-6: #F1F6FF;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background: white;
            color: var(--dark);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid var(--border);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
            align-items: center;
        }
        
        nav ul li a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            padding: 8px 16px;
            border-radius: 4px;
        }
        
        nav ul li a:hover {
            color: var(--primary);
            background-color: rgba(37, 99, 235, 0.1);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 180px 0 100px;
            text-align: center;
            position: relative;
            margin-top: 80px;
        }
        
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            background-color: white;
            color: var(--primary);
            padding: 14px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            background-color: transparent;
            border-color: white;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background-color: transparent;
            border-color: white;
            color: white;
            margin-left: 15px;
        }
        
        .btn-secondary:hover {
            background-color: white;
            color: var(--primary);
        }
        
        /* Secciones */
        section {
            padding: 100px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 60px;
            color: var(--secondary);
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            display: inline-block;
        }
        
        /* Qué es Navi */
        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            line-height: 1.8;
        }
        
        .about-list {
            margin: 25px 0;
        }
        
        .about-list li {
            margin-bottom: 15px;
            line-height: 1.8;
            font-size: 1.1rem;
            position: relative;
            padding-left: 30px;
        }
        
        .about-list li:before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .about-image {
            flex: 1;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 400px;
            background-size: cover;
            background-position: center;
            background-image: url("images/portada.jpg");
        }
        
        /* Características */
        .features {
            background-color: #f1f5f9;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            font-size: 1.3rem;
            position: relative;
            z-index: 2;
        }
        
        .feature-card p {
            position: relative;
            z-index: 2;
        }
        
        /* Colores específicos para cada característica */
        .feature-card:nth-child(1) {
            background-color: var(--color-1);
        }
        
        .feature-card:nth-child(1) .feature-icon {
            color: #3b4fce;
        }
        
        .feature-card:nth-child(1) h3 {
            color: #2d3b9e;
        }
        
        .feature-card:nth-child(2) {
            background-color: var(--color-2);
        }
        
        .feature-card:nth-child(2) .feature-icon {
            color: #5a56d9;
        }
        
        .feature-card:nth-child(2) h3 {
            color: #4541b3;
        }
        
        .feature-card:nth-child(3) {
            background-color: var(--color-3);
        }
        
        .feature-card:nth-child(3) .feature-icon {
            color: #d94cb3;
        }
        
        .feature-card:nth-child(3) h3 {
            color: #b33a94;
        }
        
        .feature-card:nth-child(4) {
            background-color: var(--color-4);
        }
        
        .feature-card:nth-child(4) .feature-icon {
            color: #d9a734;
        }
        
        .feature-card:nth-child(4) h3 {
            color: #b3892a;
        }
        
        .feature-card:nth-child(5) {
            background-color: var(--color-5);
        }
        
        .feature-card:nth-child(5) .feature-icon {
            color: #2a9d8f;
        }
        
        .feature-card:nth-child(5) h3 {
            color: #1f756b;
        }
        
        .feature-card:nth-child(6) {
            background-color: var(--color-6);
        }
        
        .feature-card:nth-child(6) .feature-icon {
            color: #3b82f6;
        }
        
        .feature-card:nth-child(6) h3 {
            color: #2563eb;
        }
        
        /* Galería */
        .gallery {
            text-align: center;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .gallery-item {
            border-radius: 12px;
            overflow: hidden;
            height: 250px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        
        .gallery-item:hover {
            transform: scale(1.03);
        }
        
        /* Investigación del mercado */
        .market-research {
            background-color: var(--secondary);
            color: white;
        }
        
        .research-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .research-content p {
            font-size: 1.1rem;
            max-width: 800px;
            margin-bottom: 20px;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-top: 50px;
        }
        
        .stat-item {
            background-color: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
            width: 30%;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin-bottom: 10px;
        }
        
        /* Objetivos */
        .objectives-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .objectives-table th, .objectives-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .objectives-table th {
            background-color: var(--primary);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .objectives-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .objectives-table tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Testimonios */
        .testimonials {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            position: relative;
            border: 1px solid var(--border);
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            line-height: 1.8;
        }
        
        .testimonial-author {
            font-weight: bold;
            color: var(--primary);
        }
        
        /* CTA */
        .cta {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
            padding: 80px 0;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .cta p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .cta .btn {
            background-color: white;
            color: var(--primary);
        }
        
        .cta .btn:hover {
            background-color: transparent;
            color: white;
            border-color: white;
        }
        
        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .footer-links h3, .footer-contact h3 {
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .footer-links ul {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-contact p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9rem;
            color: #cbd5e1;
        }
        
        /* Estilos para usuario logueado */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-welcome {
            color: var(--dark);
            font-weight: 500;
        }
        
        .logout-btn {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background-color: #ef4444;
            color: white;
        }

        /* color de investigacion mercado */
         .market-research .section-title h2 {
        color: white;
        }

        .future-goals {
        margin-bottom: 80px; /* deja espacio en blanco después de los objetivos */
        }


        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
                gap: 20px;
            }
            
            .stat-item {
                width: 100%;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-buttons {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .hero-buttons .btn {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo-container">
                <img src="images/logo.png" alt="Logo de Navi" style="height: 50px;">
            </div>
            <nav>
                <ul>
                    <li><a href="#conoce-a-navi">Conoce a NAVI</a></li>
                    <li><a href="#caracteristicas">Características</a></li>
                    <li><a href="#investigacion">Investigación</a></li>
                    <li><a href="#objetivos">Objetivos</a></li>
                    <li><a href="#galeria">Galería</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                    <?php if ($is_logged_in): ?>
                        <li class="user-menu">
                            <span class="user-welcome">Hola, <?php echo htmlspecialchars($user_name); ?></span>
                            <a href="app.php" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Ir a la App</a>
                            <a href="perfil.php" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Mi Perfil</a>
                            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h1>NAVI: Nuevas formas de aprender</h1>
            <p>Plataforma educativa creada para que niños con discapacidad visual de 3 a 7 años: aprendan, jueguen y exploren el mundo de forma autónoma y divertida.</p>
            <div class="hero-buttons">
                <?php if ($is_logged_in): ?>
                    <a href="perfil.php" class="btn">Ver Mi Perfil</a>
                <?php else: ?>
                    <a href="#conoce-a-navi" class="btn">Más Información</a>
                    <a href="login.php" class="btn btn-secondary">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Conoce a NAVI -->
    <section id="conoce-a-navi">
        <div class="container">
            <div class="section-title">
                <h2>Conoce a NAVI</h2>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p><strong>NAVI</strong> es una plataforma educativa digital inclusiva, diseñada específicamente para promover el aprendizaje y desarrollo cognitivo de niños con discapacidad visual, entre 3 y 7 años. A través de un entorno completamente sonoro, combina actividades guiadas por voz, juegos sensoriales y contenidos adaptados para garantizar una experiencia educativa accesible y significativa.</p>
                    
                    <h3 style="color: var(--primary); margin: 25px 0 15px; font-size: 1.5rem;">¿Por qué NAVI es diferente?</h3>
                    
                    <ul class="about-list">
                        <li><strong>Adaptación individual:</strong> Cada niño es único. NAVI se adapta a sus capacidades, intereses y estilo de aprendizaje</li>
                        <li><strong>Entorno seguro y controlado:</strong> Espacio divertido y apropiado que convierte el aprendizaje en un juego</li>
                        <li><strong>Enfoque multisensorial:</strong> Utiliza el sonido y el tacto como principales vías de exploración y descubrimiento</li>
                        <li><strong>Ritmo personalizado:</strong> Sin prisa, sin presión. NAVI garantiza que todos los niños avancen con confianza</li>
                        <li><strong>Interfaz completamente audible:</strong> Navegación intuitiva sin necesidad de elementos visuales</li>
                    </ul>
                </div>
                <div class="about-image"></div>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section id="caracteristicas" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Características de Navi</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-universal-access"></i>
                    </div>
                    <h3>Completamente Accesible</h3>
                    <p>Diseñada desde sus cimientos para ser utilizada por niños con discapacidad visual, con navegación intuitiva y controles adaptados.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headphones"></i>
                    </div>
                    <h3>Interfaz Sonora</h3>
                    <p>Navegación completamente auditiva con instrucciones claras y feedback sonoro para cada interacción.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3>Adaptada por Edad</h3>
                    <p>Contenido y actividades específicamente diseñadas para el rango de edad de 3 a 7 años, considerando sus capacidades cognitivas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Seguimiento Personalizado</h3>
                    <p>Monitoreo del progreso individual con ajustes automáticos según el desarrollo de cada niño.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3>Enfoque Lúdico</h3>
                    <p>Aprendizaje a través del juego, con actividades divertidas que mantienen el interés y motivación de los niños.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Entorno Seguro</h3>
                    <p>Espacio libre de distracciones y contenido inapropiado, diseñado específicamente para niños pequeños.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Galería -->
    <section id="galeria" class="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Galería de Navi</h2>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item" style="background-image: url('images/galeria1.jpg')"></div>
                <div class="gallery-item" style="background-image: url('images/galeria2.jpg')"></div>
                <div class="gallery-item" style="background-image: url('images/galeria3.jpg')"></div>
            </div>
        </div>
    </section>

    <!-- Investigación del mercado -->
    <section id="investigacion" class="market-research">
        <div class="container">
            <div class="section-title">
                <h2>Investigación del Mercado</h2>
            </div>
            <div class="research-content">
                <p>En Yucatán, donde más de 1200 niños viven con discapacidad visual (INEGI 2020), no existen plataformas educativas diseñadas específicamente para la primera infancia que sean completamente accesibles.</p>
                <p>Navi cambia este paradigma con una <strong>selección especializada</strong> y <strong>adaptación a la edad</strong> de los usuarios, llenando un vacío crítico en el mercado educativo inclusivo.</p>
                
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number">1200+</div>
                        <div class="stat-desc">Niños con discapacidad visual en Yucatán</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">3-7</div>
                        <div class="stat-desc">Años, rango de edad objetivo</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">0</div>
                        <div class="stat-desc">Plataformas similares actualmente</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Objetivos -->
    <section id="objetivos">
        <div class="container">
            <div class="section-title">
                <h2>Objetivos</h2>
            </div>
            <table class="objectives-table">
                <thead>
                    <tr>
                        <th>Indicadores</th>
                        <th>Acciones en la plataforma digital</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Crear experiencias para niños ciegos</td>
                        <td>Desarrollar contenido táctil y auditivo adaptado, con navegación por voz y sonidos direccionales</td>
                    </tr>
                    <tr>
                        <td>Fomentar el desarrollo cognitivo</td>
                        <td>Implementar juegos educativos con retroalimentación auditiva inmediata y progresiva dificultad</td>
                    </tr>
                    <tr>
                        <td>Adaptarse a necesidades individuales</td>
                        <td>Ofrecer personalización según nivel de desarrollo, preferencias y ritmo de aprendizaje</td>
                    </tr>
                    <tr>
                        <td>Promover aprendizaje lúdico</td>
                        <td>Diseñar actividades divertidas y motivadoras con sistema de recompensas auditivas</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Objetivos a futuro -->
    <section id="caracteristicas" class="features">
        <div class="container">
            <div class="section-title">
                <h2>Objetivos a Futuro</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wave-square"></i>
                    </div>
                    <h3>Aprendizaje sensorial con audio</h3>
                    <p>Desarrollo de habilidades cognitivas mediante estímulos auditivos diseñados específicamente para niños con discapacidad visual.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Biblioteca colaborativa accesible</h3>
                    <p>Plataforma comunitaria donde usuarios y educadores pueden compartir y acceder a recursos educativos en formatos accesibles.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3>Cuaderno digital con interacción sonora</h3>
                    <p>Herramienta educativa que combina la escritura con retroalimentación auditiva para reforzar el aprendizaje de conceptos básicos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-braille"></i>
                    </div>
                    <h3>Conversión de texto a braille para impresión</h3>
                    <p>Sistema que transforma contenido digital a formato braille físico, facilitando el acceso a materiales educativos impresos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3>Accesibilidad sin conexión a internet</h3>
                    <p>Funcionalidad completa disponible sin necesidad de conexión a internet, garantizando acceso continuo a los recursos educativos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <h3>Productos complementarios</h3>
                    <p>Desarrollo de herramientas y materiales adicionales que enriquecen la experiencia educativa y se adaptan a diferentes necesidades.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Registrarse navi codigo -->
    <section class="cta">
        <div class="container">
            <h2>¿Listo para conocer Navi?</h2>
            <p>Descubre cómo nuestra plataforma puede transformar la experiencia educativa para niños con discapacidad visual</p>
            <?php if ($is_logged_in): ?>
                <a href="perfil.php" class="btn">Ver Mi Perfil</a>
            <?php else: ?>
                <a href="registro.php" class="btn">Registrarse Gratis</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- informacion de contacto -->
    <footer id="contacto">
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <div class="footer-logo">
                        <img src="images/logoBlanco.png" alt="Logo de Navi" style="height: 50px;">
                        <span>Juega aprendiendo</span>
                    </div>
                    <p>Plataforma educativa inclusiva diseñada especialmente para niños ciegos de entre 3 y 7 años, fomentando su desarrollo cognitivo a través de experiencias adaptadas.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.linkedin.com/" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.youtube.com/@navimx-oficial" target="_blank"><i class="fab fa-youtube"></i></a>
                    </div>

                </div>
                <div class="footer-links">
                    <h3>Enlaces rápidos</h3>
                    <ul>
                        <li><a href="#conoce-a-navi">¿Qué es Navi?</a></li>
                        <li><a href="#caracteristicas">Características</a></li>
                        <li><a href="#investigacion">Investigación</a></li>
                        <li><a href="#objetivos">Objetivos</a></li>
                        <li><a href="#galeria">Galería</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contacto</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Yucatán, México</p>
                    <p><i class="fas fa-phone"></i> +52 999 123 4567</p>
                    <p><i class="fas fa-envelope"></i> naviacademic@gmail.com</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Navi. Todos los derechos reservados. | Plataforma educativa inclusiva</p>
            </div>
        </div>
    </footer>

    <script>
        // Efecto de header al hacer scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.padding = '10px 0';
                header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
            } else {
                header.style.padding = '15px 0';
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
            }
        });
        
        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>