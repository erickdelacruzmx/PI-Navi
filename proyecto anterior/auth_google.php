<?php
session_start();

// Configuración
$CLIENT_ID = "48066165712-8cuvi19q46qgvhdvupgsfb9fqj5kioij.apps.googleusercontent.com"; // Tu ID de google-services.json

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos (soporta JSON raw y POST normal)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Si viene de la App Android o Web JSON
    $token = $data['token'] ?? $_POST['credential'] ?? null;

    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Token no proporcionado']);
        exit;
    }

    // Verificar el token directamente con Google (Sin librerías externas para compatibilidad simple)
    $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $token;
    
    // Usar cURL o file_get_contents
    $response = @file_get_contents($url);
    
    if ($response === FALSE) {
        echo json_encode(['success' => false, 'message' => 'Token inválido o error de conexión con Google']);
        exit;
    }

    $payload = json_decode($response, true);

    if (isset($payload['aud']) && $payload['aud'] === $CLIENT_ID) {
        // --- AUTENTICACIÓN EXITOSA ---
        
        // Guardar datos en sesión
        $_SESSION['user_email'] = $payload['email'];
        $_SESSION['user_name'] = $payload['name'];
        $_SESSION['user_picture'] = $payload['picture'] ?? ''; // Foto de perfil
        $_SESSION['user_type'] = "Tutor"; // Por defecto
        $_SESSION['logged_in'] = true;
        $_SESSION['social_login'] = true;
        $_SESSION['auth_provider'] = 'google';

        // Respuesta para la App o Web
        echo json_encode([
            'success' => true, 
            'redirect' => 'app_backup.php',
            'user' => [
                'name' => $payload['name'],
                'email' => $payload['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Token no corresponde a esta aplicación']);
    }
    exit;
}
?>