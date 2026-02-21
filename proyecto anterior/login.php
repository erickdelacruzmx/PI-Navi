<?php
session_start();

// Si el usuario ya está logueado, redirigir al index
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php'); // O app.php según tu flujo
    exit;
}

// Procesar formulario de login TRADICIONAL
$login_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validaciones
    if (empty($email)) $login_errors[] = "El correo electrónico es obligatorio";
    if (empty($password)) $login_errors[] = "La contraseña es obligatoria";
    
    // Login tradicional
    if (empty($login_errors)) {
        $valid_email = "usuario@ejemplo.com";
        $valid_password = "password123";
        
        if ($email === $valid_email && $password === $valid_password) {
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = "Usuario Ejemplo";
            $_SESSION['user_type'] = "Tutor";
            $_SESSION['logged_in'] = true;
            header('Location: app.php');
            exit;
        } else {
            $login_errors[] = "Credenciales incorrectas. Use: usuario@ejemplo.com / password123";
        }
    }
}

// Procesar login con FACEBOOK (Tu simulación original)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['social_login']) && $_POST['provider'] === 'facebook') {
    $_SESSION['user_email'] = "usuario_facebook@ejemplo.com";
    $_SESSION['user_name'] = "Usuario Facebook";
    $_SESSION['user_type'] = "Tutor";
    $_SESSION['logged_in'] = true;
    $_SESSION['social_login'] = true;
    
    header('Location: app.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Navi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        /* Tus estilos originales para el loading */
        .social-btn.loading { position: relative; overflow: hidden; }
        .social-btn.loading::after { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: loading 1.5s infinite; }
        @keyframes loading { 0% { left: -100%; } 100% { left: 100%; } }
        .social-form { margin: 0; padding: 0; }

        /* ESTILOS PARA EL TRUCO DEL BOTÓN DE GOOGLE */
        .google-btn-wrapper {
            position: relative;
            width: 100%;
            margin-bottom: 1px; /* Espacio entre botones */
        }

        /* El contenedor invisible que tendrá el botón real de Google */
        #google_btn_overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10; /* Asegura que esté encima del botón visual */
            opacity: 0.01; /* Casi invisible, pero clickeable */
            overflow: hidden;
        }
        
        /* Asegurar que el botón visual se vea pero no interfiera */
        .social-btn.google {
            width: 100%; /* Asegurar ancho completo */
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="auth-form-container">
                <div class="form-header">
                    <h1>¡Hola!</h1>
                    <h2>Iniciar Sesión</h2>
                    <p>Bienvenido a Navi.</p>
                </div>
                
                <?php if (!empty($login_errors)): ?>
                    <?php foreach ($login_errors as $error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="demo-credentials">
                    <strong>Credenciales de prueba:</strong><br>
                    Email: usuario@ejemplo.com<br>
                    Contraseña: password123
                </div>
                
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="login-email">Correo electrónico</label>
                        <input type="email" id="login-email" name="email" class="form-control" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Contraseña</label>
                        <input type="password" id="login-password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="#">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <button type="submit" class="btn">Ingresar</button>
                </form>
                
                <div class="social-login">
                    <p>Ingresa con</p>
                    
                    <div class="social-buttons" style="flex-direction: column;"> <div class="google-btn-wrapper">
                            <button type="button" class="social-btn google">
                                <i class="fab fa-google"></i>
                                Continuar con Google
                            </button>
                            
                            <div id="google_btn_overlay"></div>
                        </div>

                        <form method="POST" class="social-form" style="width: 100%;">
                            <input type="hidden" name="social_login" value="1">
                            <input type="hidden" name="provider" value="facebook">
                            <button type="submit" class="social-btn facebook" style="width: 100%;">
                                <i class="fab fa-facebook-f"></i>
                                Continuar con Facebook
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="form-footer">
                    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
                </div>
                
                <div class="back-home">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Volver a la página principal</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. MANEJO DE GOOGLE
        function handleCredentialResponse(response) {
            console.log("Token recibido: " + response.credential);
            
            // Efecto visual de carga en tu botón (opcional, para feedback)
            const visualBtn = document.querySelector('.social-btn.google');
            if(visualBtn) {
                visualBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                visualBtn.classList.add('loading');
            }

            // Enviar a tu backend PHP
            fetch('auth_google.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: response.credential })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert("Error: " + data.message);
                    if(visualBtn) {
                        visualBtn.innerHTML = '<i class="fab fa-google"></i> Continuar con Google';
                        visualBtn.classList.remove('loading');
                    }
                }
            })
            .catch(e => console.error(e));
        }

        window.onload = function () {
            // Inicializar Google
            google.accounts.id.initialize({
                client_id: "48066165712-8cuvi19q46qgvhdvupgsfb9fqj5kioij.apps.googleusercontent.com",
                callback: handleCredentialResponse,
                auto_prompt: false // Desactivar popup automático si prefieres solo click
            });
            
            // Renderizar botón invisible sobre el tuyo
            // Usamos un ancho grande para asegurar cobertura
            google.accounts.id.renderButton(
                document.getElementById("google_btn_overlay"),
                { 
                    theme: "outline", 
                    size: "large", 
                    type: "standard",
                    width: 400 // Forzamos ancho grande para cubrir tu botón
                }
            );
        };

        // 2. MANEJO DE FACEBOOK (Tu código original)
        document.addEventListener('DOMContentLoaded', function() {
            const socialForms = document.querySelectorAll('.social-form');
            socialForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('.social-btn.facebook');
                    if (button) {
                        button.classList.add('loading');
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                        setTimeout(() => {
                            if (button.disabled) {
                                button.classList.remove('loading');
                                button.disabled = false;
                                button.innerHTML = `<i class="fab fa-facebook-f"></i> Continuar con Facebook`;
                            }
                        }, 3000);
                    }
                });
            });
        });
    </script>
</body>
</html>