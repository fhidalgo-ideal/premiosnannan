<?php
header('Content-Type: application/json');
require_once 'db.php';

$desvelar = isset($_GET['desvelar']) && $_GET['desvelar'] === '1';

try {
    if ($desvelar) {
        // Muestra los nombres reales de los chefs
        $sql = "SELECT c.nombre AS etiqueta, COUNT(v.id) AS votos 
                FROM chefs c 
                LEFT JOIN votos v ON c.id = v.chef_id 
                GROUP BY c.id 
                ORDER BY c.id ASC";
    } else {
        // Muestra las etiquetas anónimas (Plato 01, Plato 02...)
        $sql = "SELECT c.etiqueta_anonima AS etiqueta, COUNT(v.id) AS votos 
                FROM chefs c 
                LEFT JOIN votos v ON c.id = v.chef_id 
                GROUP BY c.id 
                ORDER BY c.id ASC";
    }

    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $resultados, 'desvelado' => $desvelar]);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}