<?php
header('Content-Type: application/json');
require_once 'db.php';
require_once 'config.php';

// Clave Secreta de reCAPTCHA v3

// 1. Obtener los datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. Extraer variables
$tapa_id = isset($data['tapa_id']) ? intval($data['tapa_id']) : 0;
$fingerprint = isset($data['fingerprint']) ? trim($data['fingerprint']) : '';
$recaptcha_token = isset($data['recaptcha_token']) ? trim($data['recaptcha_token']) : '';

// 3. Detectar si estamos en entorno local
$esLocalhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['REMOTE_ADDR'] === '127.0.0.1' || 
    $_SERVER['REMOTE_ADDR'] === '::1'
);

// 4. Validar entradas mínimas
if ($tapa_id <= 0 || empty($fingerprint)) {
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida o falta verificación del dispositivo.']);
    exit;
}

// En producción exigimos obligatoriamente el token
if (!$esLocalhost && empty($recaptcha_token)) {
    echo json_encode(['success' => false, 'message' => 'Falta la verificación de seguridad reCAPTCHA.']);
    exit;
}

// 5. Validar con Google reCAPTCHA v3 (solo si NO estamos en local)
if (!$esLocalhost) {
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $response = @file_get_contents($verifyUrl . '?secret=' . RECAPTCHA_SECRET_KEY . '&response=' . $recaptcha_token);

    if ($response !== false) {
        $responseData = json_decode($response, true);
        $score = $responseData['score'] ?? 0;

        // Umbral permisivo de 0.2 para evitar bloqueos a usuarios legítimos en móvil/incógnito
        if (!$responseData['success'] || $score < 0.2) {
            echo json_encode(['success' => false, 'message' => 'El sistema ha detectado comportamiento sospechoso. Voto no permitido.']);
            exit;
        }
    }
}

try {
    // 6. Comprobar si la huella (fingerprint) o la cookie ya han votado
    if (isset($_COOKIE['voto_registrado'])) {
        echo json_encode(['success' => false, 'message' => 'Este navegador ya ha registrado un voto.']);
        exit;
    }

    $checkStmt = $pdo->prepare("SELECT id FROM votos WHERE fingerprint = ?");
    $checkStmt->execute([$fingerprint]);

    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este dispositivo ya ha registrado un voto.']);
        exit;
    }

    // 7. Registrar voto
    $insertStmt = $pdo->prepare("INSERT INTO votos (tapa_id, fingerprint) VALUES (?, ?)");
    
    if ($insertStmt->execute([$tapa_id, $fingerprint])) {
        // Establecer cookie HTTP-Only de respaldo
        setcookie('voto_registrado', '1', [
            'expires' => time() + (86400 * 365),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        echo json_encode(['success' => true, 'message' => '¡Voto registrado con éxito! Gracias por participar.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el voto.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la base de datos.']);
}