<?php
    session_start();

    // =========================================================================
    // 1. LÓGICA DE API GOOGLE (BACKEND ACTUALIZADO PARA POPUP)
    // =========================================================================
    $input_json = file_get_contents('php://input');
    $data_api = json_decode($input_json, true);

    if ($data_api && isset($data_api['token'])) {
        header('Content-Type: application/json');
        
        $client_id = "48066165712-8cuvi19q46qgvhdvupgsfb9fqj5kioij.apps.googleusercontent.com";
        $access_token = $data_api['token'];

        // CAMBIO IMPORTANTE: Verificamos usando 'userinfo' porque ahora usamos Access Token
        // Esta URL obtiene los datos del perfil del usuario directamente
        $url = "https://www.googleapis.com/oauth2/v3/userinfo?access_token=" . $access_token;
        
        // Usamos context para manejar posibles errores de conexión limpiamente
        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: NaviApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $payload = json_decode($response, true);
            
            // Si Google nos devuelve el email ('email_verified' es opcional pero recomendado revisar)
            if (isset($payload['sub']) && isset($payload['email'])) {
                
                // --- ÉXITO: Usuario autenticado ---
                $_SESSION['user_name'] = $payload['name'];
                $_SESSION['user_email'] = $payload['email'];
                $_SESSION['user_picture'] = $payload['picture'] ?? '';
                $_SESSION['user_type'] = "Tutor";
                $_SESSION['logged_in'] = true;
                $_SESSION['social_login'] = true;
                $_SESSION['auth_provider'] = 'google';

                echo json_encode(['success' => true, 'redirect' => 'app.php']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo verificar la información del usuario.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Token inválido o expirado.']);
        }
        exit; 
    }

    // =========================================================================
    // 2. LÓGICA DE REGISTRO MANUAL (PHP TRADICIONAL)
    // =========================================================================

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: index.php');
        exit;
    }

    $register_errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($name)) $register_errors[] = "El nombre es obligatorio";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $register_errors[] = "El correo electrónico no es válido";
        if (empty($password) || strlen($password) < 6) $register_errors[] = "La contraseña debe tener al menos 6 caracteres";
        if ($password !== $confirm_password) $register_errors[] = "Las contraseñas no coinciden";
        
        if (empty($register_errors)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = "Tutor";
            $_SESSION['logged_in'] = true;
            header('Location: app.php');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Tutor - Navi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: #f8f9fa; color: #333; line-height: 1.6; }
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .container { width: 100%; max-width: 450px; }
        .auth-form-container { background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 40px; width: 100%; }
        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header h1 { font-size: 28px; font-weight: 700; color: #2563eb; margin-bottom: 10px; }
        .form-header h2 { font-size: 24px; font-weight: 600; margin-bottom: 15px; color: #1f2937; }
        .form-header p { color: #6b7280; font-size: 16px; margin-bottom: 5px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
        .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .btn { width: 100%; padding: 14px; background-color: #2563eb; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-bottom: 25px; }
        .btn:hover { background-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        .form-footer { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .form-footer p { color: #6b7280; }
        .form-footer a { color: #2563eb; text-decoration: none; font-weight: 500; }
        .form-footer a:hover { text-decoration: underline; }
        .back-home { text-align: center; margin-top: 25px; }
        .back-home a { color: #6b7280; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .back-home a:hover { color: #374151; }
        .error-message { background-color: #fee2e2; color: #dc2626; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626; }
        
        .social-register { margin-top: 25px; text-align: center; }
        .social-register p { color: #6b7280; margin-bottom: 20px; font-size: 14px; position: relative; }
        .social-register p::before, .social-register p::after { content: ""; position: absolute; top: 50%; width: 30%; height: 1px; background-color: #e5e7eb; }
        .social-register p::before { left: 0; }
        .social-register p::after { right: 0; }
        .social-buttons { display: flex; flex-direction: column; gap: 12px; }
        
        .social-btn { display: flex; align-items: center; justify-content: center; width: 100%; padding: 12px 16px; border: none; border-radius: 8px; color: white; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.3s ease; gap: 12px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .social-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .social-btn:active { transform: translateY(0); }
        
        .social-btn.google { background: linear-gradient(135deg, #4285F4, #34A853, #FBBC05, #EA4335); background-size: 400% 400%; animation: gradientShift 4s ease infinite; }
        .social-btn.google:hover { background: linear-gradient(135deg, #3367D6, #2E8B57, #F4B400, #D14836); box-shadow: 0 4px 15px rgba(234, 67, 53, 0.3); }
        .social-btn.google i { background: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #4285F4; font-weight: bold; }
        
        .social-btn.facebook { background: linear-gradient(135deg, #1877F2, #4267B2); }
        .social-btn.facebook:hover { background: linear-gradient(135deg, #166FE5, #3B5998); box-shadow: 0 4px 15px rgba(24, 119, 242, 0.3); }
        .social-btn.facebook i { background: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #1877F2; }
        
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        /* Modal (Solo para Facebook) */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; }
        .modal-close { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 20px; cursor: pointer; color: #666; }
        .modal-close:hover { color: #333; }
        .provider-logo { font-size: 48px; margin-bottom: 20px; }
        .google-logo { color: #4285F4; }
        .facebook-logo { color: #1877F2; }
        .modal-title { font-size: 24px; font-weight: 600; margin-bottom: 10px; color: #1f2937; }
        .modal-text { color: #6b7280; margin-bottom: 25px; line-height: 1.5; }
        .modal-buttons { display: flex; gap: 10px; justify-content: center; }
        .modal-btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .modal-btn.primary { background: #2563eb; color: white; }
        .modal-btn.primary:hover { background: #1d4ed8; transform: translateY(-2px); }
        .modal-btn.secondary { background: #e5e7eb; color: #374151; }
        .modal-btn.secondary:hover { background: #d1d5db; }
    </style>
</head>
<body>
    
    <div class="auth-container">
        <div class="container">
            <div class="auth-form-container">
                <div class="form-header">
                    <h1>¡Bienvenido a Navi!</h1>
                    <p>Aprende escuchando</p>
                    <h2>Registrate</h2>
                </div>
                
                <?php if (!empty($register_errors)): ?>
                    <?php foreach ($register_errors as $error): ?>
                        <div class="error-message"><?php echo $error; ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="register" value="1">
                    <div class="form-group">
                        <label for="register-name">Nombre</label>
                        <input type="text" id="register-name" name="name" class="form-control" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="register-email">Correo electrónico</label>
                        <input type="email" id="register-email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="register-password">Contraseña</label>
                        <input type="password" id="register-password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="register-confirm-password">Confirmar contraseña</label>
                        <input type="password" id="register-confirm-password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn">Registrarme</button>
                </form>
                
                <div class="social-register">
                    <p>o registrate con</p>
                    <div class="social-buttons">
                        <button type="button" class="social-btn google" onclick="iniciarGooglePopup()">
                            <i class="fab fa-google"></i>
                            Registrarse con Google
                        </button>
                        
                        <button type="button" class="social-btn facebook" onclick="openSocialModal('facebook')">
                            <i class="fab fa-facebook-f"></i>
                            Registrarse con Facebook
                        </button>
                    </div>
                </div>
                
                <div class="form-footer">
                    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                </div>
                <div class="back-home">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Volver a la página principal</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="socialModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeSocialModal()">&times;</button>
            
            <div class="provider-logo" id="modalLogo">
                <i class="fab fa-google google-logo"></i>
            </div>
            
            <h3 class="modal-title" id="modalTitle">Iniciar sesión</h3>
            <p class="modal-text" id="modalText">
                Redirigiendo...
            </p>
            
            <div class="modal-buttons">
                <button class="modal-btn secondary" onclick="closeSocialModal()">Cancelar</button>
                
                <form method="POST" style="display: inline;" id="socialForm">
                    <input type="hidden" name="social_register" value="1">
                    <input type="hidden" name="provider" id="modalProvider" value="">
                    <button type="submit" class="modal-btn primary">Continuar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // VARIABLE PARA EL CLIENTE DE GOOGLE
        let tokenClient;

        // =====================================================================
        // 3. INICIALIZACIÓN API GOOGLE (Token Client para Popup)
        // =====================================================================
        window.onload = function() {
            if(typeof google !== 'undefined') {
                // Usamos 'oauth2.initTokenClient' en lugar de 'id.initialize'
                // Esto es lo que permite usar tu propio botón para abrir el popup
                tokenClient = google.accounts.oauth2.initTokenClient({
                    client_id: "48066165712-8cuvi19q46qgvhdvupgsfb9fqj5kioij.apps.googleusercontent.com",
                    scope: "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile",
                    callback: (response) => {
                        if (response.access_token) {
                            handleGoogleSuccess(response.access_token);
                        }
                    },
                });
            }
        }

        // Función que se ejecuta al dar clic en TU botón
        function iniciarGooglePopup() {
            if (tokenClient) {
                // Esto fuerza la apertura de la ventana emergente
                tokenClient.requestAccessToken();
            } else {
                alert("La API de Google aún no se ha cargado. Espera un momento.");
            }
        }

        // Enviar el Access Token al PHP
        function handleGoogleSuccess(accessToken) {
            console.log("Token obtenido. Verificando...");
            
            fetch('registro.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: accessToken })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || 'app.php';
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // --- Funciones para el modal (Solo Facebook) ---
        function openSocialModal(provider) {
            const modal = document.getElementById('socialModal');
            const modalLogo = document.getElementById('modalLogo');
            const modalTitle = document.getElementById('modalTitle');
            const modalText = document.getElementById('modalText');
            const modalProvider = document.getElementById('modalProvider');
            
            if (provider === 'facebook') {
                modalLogo.innerHTML = '<i class="fab fa-facebook facebook-logo"></i>';
                modalTitle.textContent = 'Iniciar sesión con Facebook';
                modalText.textContent = 'Serás redirigido a la página de autenticación de Facebook para completar tu registro.';
            }
            
            modalProvider.value = provider;
            modal.style.display = 'flex';
        }
        
        function closeSocialModal() {
            document.getElementById('socialModal').style.display = 'none';
        }
        
        document.getElementById('socialForm').addEventListener('submit', function(e) { });
        
        document.getElementById('socialModal').addEventListener('click', function(e) {
            if (e.target === this) closeSocialModal();
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const socialButtons = document.querySelectorAll('.social-btn');
            socialButtons.forEach(button => {
                button.addEventListener('mouseenter', function() { this.style.transform = 'translateY(-2px) scale(1.02)'; });
                button.addEventListener('mouseleave', function() { this.style.transform = 'translateY(0) scale(1)'; });
            });
        });
    </script>
</body>
</html>