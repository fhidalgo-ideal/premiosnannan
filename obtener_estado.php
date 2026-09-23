<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'db.php';

try {
    // Consultar la fila específica donde la clave es 'votacion_abierta'
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = 'votacion_abierta'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Evaluamos el valor: será true solo si es '1' o 1. Si es '0', será false.
    $estado_votacion = ($row && ($row['valor'] === '1' || $row['valor'] === 1));

    echo json_encode([
        'success' => true,
        'votacion_abierta' => $estado_votacion
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'votacion_abierta' => true
    ]);
}