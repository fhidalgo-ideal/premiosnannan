<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php';

try {
    $conexion = isset($pdo) ? $pdo : (isset($conn) ? $conn : null);

    if (!$conexion) {
        throw new Exception("No se encontró la variable de conexión \$pdo o \$conn en db.php");
    }

    // 1. Comprobar la clave de desvelado en la BD
    $desvelado = false;
    $stmtConfig = $conexion->prepare("SELECT valor FROM configuracion WHERE clave = 'desvelar_ganadores' LIMIT 1");
    if ($stmtConfig->execute()) {
        $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);
        if ($config && $config['valor'] === '1') {
            $desvelado = true;
        }
    }

    if (isset($_GET['desvelar']) && $_GET['desvelar'] == '1') {
        $desvelado = true;
    }

    // 2. Consulta SQL incluyendo los datos del chef asociando 'chefs.id = tapas.chef_id'
    if ($desvelado) {
        $sql = "SELECT 
                    t.id, 
                    t.nombre_tapa AS etiqueta, 
                    COUNT(v.id) AS votos, 
                    t.foto_tapa,
                    c.nombre AS nombre_chef,
                    c.restaurante,
                    c.foto AS foto_chef
                FROM tapas t 
                LEFT JOIN votos v ON t.id = v.tapa_id 
                LEFT JOIN chefs c ON t.chef_id = c.id
                GROUP BY t.id, t.nombre_tapa, t.foto_tapa, c.nombre, c.restaurante, c.foto 
                ORDER BY votos DESC, t.id ASC";
    } else {
        $sql = "SELECT 
                t.id, 
                CONCAT('Tapa ', t.id) AS etiqueta, 
                COUNT(v.id) AS votos, 
                t.foto_tapa,
                NULL AS nombre_chef,
                NULL AS restaurante,
                NULL AS foto_chef
            FROM tapas t 
            LEFT JOIN votos v ON t.id = v.tapa_id 
            GROUP BY t.id, t.foto_tapa 
            ORDER BY t.id ASC";
}

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $tapas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'desvelado' => $desvelado,
        'data' => $tapas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error ejecutando la consulta: ' . $e->getMessage(),
        'data' => []
    ]);
}