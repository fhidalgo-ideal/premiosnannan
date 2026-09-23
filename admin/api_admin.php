<?php
header('Content-Type: application/json');
require_once 'auth.php';
require_once '../db.php';

$action = $_GET['action'] ?? '';

try {
    // 1. Obtener estado de configuración y métricas
    if ($action === 'get_status') {
        $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
        $config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmtVotos = $pdo->query("SELECT COUNT(id) AS total_votos FROM votos");
        $votos = $stmtVotos->fetch();

        echo json_encode([
            'success' => true,
            'votacion_abierta' => ($config['votacion_abierta'] ?? '1') === '1',
            'desvelar_ganadores' => ($config['desvelar_ganadores'] ?? '0') === '1',
            'total_votos' => intval($votos['total_votos'] ?? 0)
        ]);
        exit;
    }

    // 2. Alternar Estado de la Votación
        if ($action === 'toggle_votacion') {
        $input = json_decode(file_get_contents('php://input'), true);
        // Si $input['estado'] es false, guardamos '0', si es true guardamos '1'
        $nuevoEstado = (isset($input['estado']) && $input['estado']) ? '1' : '0';

        $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'votacion_abierta'");
        $stmt->execute([$nuevoEstado]);

        echo json_encode(['success' => true, 'votacion_abierta' => ($nuevoEstado === '1')]);
        exit;
    }

    // 3. Alternar Desvelado de Ganadores
    if ($action === 'toggle_desvelar') {
        $input = json_decode(file_get_contents('php://input'), true);
        $nuevo_estado = !empty($input['estado']) ? '1' : '0';

        $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'desvelar_ganadores'");
        $stmt->execute([$nuevo_estado]);

        echo json_encode(['success' => true, 'estado' => $nuevo_estado === '1']);
        exit;
    }

    // 4. Listar Chefs y Tapas completas
    if ($action === 'get_tapas_chefs') {
        $sql = "SELECT t.id AS tapa_id, t.nombre_tapa, t.foto_tapa, 
                       c.id AS chef_id, c.nombre AS chef_nombre, c.restaurante, c.foto AS chef_foto
                FROM tapas t
                INNER JOIN chefs c ON t.chef_id = c.id
                ORDER BY c.id ASC, t.id ASC";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll();

        // Obtener la lista simple de chefs para el desplegable del formulario
        $stmtChefs = $pdo->query("SELECT id, nombre, restaurante FROM chefs ORDER BY nombre ASC");
        $chefs = $stmtChefs->fetchAll();

        echo json_encode(['success' => true, 'data' => $data, 'chefs' => $chefs]);
        exit;
    }

    // 5. Guardar / Crear Chef
    if ($action === 'guardar_chef') {
        $nombre = trim($_POST['nombre'] ?? '');
        $restaurante = trim($_POST['restaurante'] ?? '');
        
        if (empty($nombre) || empty($restaurante)) {
            echo json_encode(['success' => false, 'error' => 'El nombre y restaurante son obligatorios.']);
            exit;
        }

        $foto_nombre = 'default-chef.jpg';
        if (isset($_FILES['foto_chef']) && $_FILES['foto_chef']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto_chef']['name'], PATHINFO_EXTENSION);
            $foto_nombre = 'chef_' . time() . '_' . uniqid() . '.' . strtolower($ext);
            move_uploaded_file($_FILES['foto_chef']['tmp_name'], '../img/' . $foto_nombre);
        }

        $stmt = $pdo->prepare("INSERT INTO chefs (nombre, restaurante, foto) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $restaurante, $foto_nombre]);

        echo json_encode(['success' => true, 'message' => 'Chef añadido correctamente.']);
        exit;
    }

    // 6. Guardar / Crear Tapa (vinculada a un Chef)
    if ($action === 'guardar_tapa') {
        $chef_id = intval($_POST['chef_id'] ?? 0);
        $nombre_tapa = trim($_POST['nombre_tapa'] ?? '');

        if ($chef_id <= 0 || empty($nombre_tapa)) {
            echo json_encode(['success' => false, 'error' => 'Debes seleccionar un chef e indicar el nombre de la tapa.']);
            exit;
        }

        $foto_tapa_nombre = 'default-tapa.jpg';
        if (isset($_FILES['foto_tapa']) && $_FILES['foto_tapa']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['foto_tapa']['name'], PATHINFO_EXTENSION);
            $foto_tapa_nombre = 'tapa_' . time() . '_' . uniqid() . '.' . strtolower($ext);
            move_uploaded_file($_FILES['foto_tapa']['tmp_name'], '../img/' . $foto_tapa_nombre);
        }

        $stmt = $pdo->prepare("INSERT INTO tapas (chef_id, nombre_tapa, foto_tapa) VALUES (?, ?, ?)");
        $stmt->execute([$chef_id, $nombre_tapa, $foto_tapa_nombre]);

        echo json_encode(['success' => true, 'message' => 'Tapa registrada con éxito.']);
        exit;
    }

    // 7. Eliminar Tapa
    if ($action === 'eliminar_tapa') {
        $input = json_decode(file_get_contents('php://input'), true);
        $tapa_id = intval($input['tapa_id'] ?? 0);

        if ($tapa_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM tapas WHERE id = ?");
            $stmt->execute([$tapa_id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }

    // 8. Exportar CSV
    if ($action === 'exportar_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=acta_resultados_nannan.csv');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['ID Tapa', 'Nombre Tapa', 'Chef', 'Restaurante', 'Total Votos']);

        $sql = "SELECT t.id, t.nombre_tapa, c.nombre AS chef, c.restaurante, COUNT(v.id) AS total_votos
                FROM tapas t
                INNER JOIN chefs c ON t.chef_id = c.id
                LEFT JOIN votos v ON t.id = v.tapa_id
                GROUP BY t.id
                ORDER BY total_votos DESC";

        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Acción no válida.']);

} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}