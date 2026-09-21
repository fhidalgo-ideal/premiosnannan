<?php
header('Content-Type: application/json');
require_once 'db.php';

$desvelar = isset($_GET['desvelar']) && $_GET['desvelar'] === '1';

try {
    if ($desvelar) {
        // Muestra Tapa — Chef [Restaurante]
        $sql = "SELECT CONCAT(t.nombre_tapa, ' — ', c.nombre, ' [', c.restaurante, ']') AS etiqueta, 
                       COUNT(v.id) AS votos 
                FROM tapas t
                INNER JOIN chefs c ON t.chef_id = c.id
                LEFT JOIN votos v ON t.id = v.tapa_id 
                GROUP BY t.id 
                ORDER BY t.id ASC";
    } else {
        // Muestra únicamente el Nombre de la Tapa
        $sql = "SELECT t.nombre_tapa AS etiqueta, 
                       COUNT(v.id) AS votos 
                FROM tapas t
                LEFT JOIN votos v ON t.id = v.tapa_id 
                GROUP BY t.id 
                ORDER BY t.id ASC";
    }

    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $resultados, 'desvelado' => $desvelar]);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}