<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$chef_id = isset($data['chef_id']) ? intval($data['chef_id']) : 0;

if ($chef_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Selección no válida.']);
    exit;
}

// Huella digital simple (IP + User Agent)
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip_hash = hash('sha256', $ip . $user_agent);

// Verificar si ya votó
$checkStmt = $pdo->prepare("SELECT id FROM votos WHERE ip_hash = ?");
$checkStmt->execute([$ip_hash]);

if ($checkStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Ya has emitido tu voto desde este dispositivo.']);
    exit;
}

// Registrar voto
$insertStmt = $pdo->prepare("INSERT INTO votos (chef_id, ip_hash) VALUES (?, ?)");
if ($insertStmt->execute([$chef_id, $ip_hash])) {
    echo json_encode(['success' => true, 'message' => '¡Voto registrado con éxito! Gracias por participar.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el voto.']);
}