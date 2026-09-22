<?php
header('Content-Type: application/json');
require_once 'db.php'; // Tu archivo de conexión a la BD

try {
    // Si tu tabla de configuración guarda el estado, consultamos
    $stmt = $pdo->query("SELECT * FROM configuracion LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe la columna o la consulta devuelve vacío, dejamos abierto por defecto (true)
    $estado_votacion = true;

    if ($config) {
        // Ajusta 'votacion_abierta' o 'estado' según el nombre real de tu columna en la BD
        if (isset($config['votacion_abierta'])) {
            $estado_votacion = (bool)$config['votacion_abierta'];
        } elseif (isset($config['estado'])) {
            $estado_votacion = ($config['estado'] == 1 || $config['estado'] === 'abierta');
        }
    }

    echo json_encode([
        'success' => true,
        'votacion_abierta' => $estado_votacion
    ]);
} catch (Exception $e) {
    // Si la tabla no existe o falla la BD, permitimos mostrar las tapas por defecto
    echo json_encode([
        'success' => true, 
        'votacion_abierta' => true
    ]);
}