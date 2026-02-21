<?php
session_start();

// Procesar login con Facebook
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $accessToken = $data['accessToken'];
    $user = $data['user'];
    
    // Verificar el token con Facebook (simplificado)
    // En producción, usarías la biblioteca oficial de Facebook
    
    // Simulación de verificación exitosa
    $_SESSION['user_email'] = $user['email'] ?? "usuario_facebook@ejemplo.com";
    $_SESSION['user_name'] = $user['name'] ?? "Usuario Facebook";
    $_SESSION['user_type'] = "Tutor";
    $_SESSION['logged_in'] = true;
    $_SESSION['social_login'] = true;
    
    echo json_encode(['success' => true, 'redirect' => 'app.php']);
    exit;
}
?>