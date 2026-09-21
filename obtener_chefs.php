<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Obtenemos solo los datos de las tapas
    $stmt = $pdo->query("SELECT id, nombre_tapa, foto_tapa FROM tapas ORDER BY id ASC");
    $tapas = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $tapas]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}