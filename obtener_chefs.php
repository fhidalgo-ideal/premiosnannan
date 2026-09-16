<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT id, nombre, foto FROM chefs ORDER BY id ASC");
    $chefs = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $chefs]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}