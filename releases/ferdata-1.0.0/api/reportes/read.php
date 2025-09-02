<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;

try {
    // Quick guard: if the reportes table doesn't exist in this database, return empty list
    try {
        $checkStmt = $db->prepare("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'reportes'");
        $checkStmt->execute();
        $chk = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$chk || intval($chk['cnt']) === 0) {
            http_response_code(200);
            echo json_encode([]);
            exit(0);
        }
    } catch (Exception $ci) {
        // If information_schema isn't accessible, fall through and let the regular queries run and be caught
    }
    if ($id) {
    // Populate usuario_nombre using a safe scalar subquery that only uses known columns
    $query = "SELECT r.*, e.nombre as empresa_nombre, (SELECT COALESCE(u.nombre_completo, u.email) FROM usuarios u WHERE u.id = r.usuario_id LIMIT 1) as usuario_nombre FROM reportes r LEFT JOIN empresas e ON r.empresa_id = e.id WHERE r.id = ? LIMIT 0,1";
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
        } catch (PDOException $pe) {
            if (strpos($pe->getMessage(), 'Base table or view not found') !== false) {
                http_response_code(200);
                echo json_encode([]);
                exit(0);
            }
            throw $pe;
        }
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Prefer usuario_nombre from the SQL subquery, fallback to manual lookup
            $usuario_nombre = isset($row['usuario_nombre']) && $row['usuario_nombre'] ? $row['usuario_nombre'] : null;
            if (empty($usuario_nombre) && !empty($row['usuario_id'])) {
                try {
                    $uStmt = $db->prepare('SELECT nombre_completo, email FROM usuarios WHERE id = ? LIMIT 1');
                    $uStmt->bindParam(1, $row['usuario_id']);
                    $uStmt->execute();
                    if ($uStmt->rowCount() > 0) {
                        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                        if (!empty($uRow['nombre_completo'])) $usuario_nombre = $uRow['nombre_completo'];
                        elseif (!empty($uRow['email'])) $usuario_nombre = $uRow['email'];
                    }
                } catch (Exception $ue) { /* ignore and leave usuario_nombre null */ }
            }
            $report = array(
                'id' => isset($row['id']) ? $row['id'] : null,
                'nombre' => isset($row['nombre']) ? $row['nombre'] : null,
                'tipo' => isset($row['tipo']) ? $row['tipo'] : null,
                'formato' => isset($row['formato']) ? $row['formato'] : null,
                'empresa_id' => isset($row['empresa_id']) ? $row['empresa_id'] : null,
                'empresa_nombre' => isset($row['empresa_nombre']) ? $row['empresa_nombre'] : null,
                'usuario_id' => isset($row['usuario_id']) ? $row['usuario_id'] : null,
                'usuario_nombre' => $usuario_nombre,
                'fecha_generacion' => isset($row['fecha_generacion']) ? $row['fecha_generacion'] : null,
                'url' => isset($row['url']) ? $row['url'] : null
            );
            echo json_encode($report);
            http_response_code(200);
            exit(0);
        }
        http_response_code(404);
        echo json_encode(array('message' => 'Reporte no encontrado.'));
        exit(0);
    } else {
    $query = "SELECT r.*, e.nombre as empresa_nombre, (SELECT COALESCE(u.nombre_completo, u.email) FROM usuarios u WHERE u.id = r.usuario_id LIMIT 1) as usuario_nombre FROM reportes r LEFT JOIN empresas e ON r.empresa_id = e.id ORDER BY r.fecha_generacion DESC";
        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $pe) {
            if (strpos($pe->getMessage(), 'Base table or view not found') !== false) {
                http_response_code(200);
                echo json_encode([]);
                exit(0);
            }
            throw $pe;
        }
        $out = array();
        foreach ($rows as $row) {
            $usuario_nombre = isset($row['usuario_nombre']) && $row['usuario_nombre'] ? $row['usuario_nombre'] : null;
            if (empty($usuario_nombre) && !empty($row['usuario_id'])) {
                try {
                    $uStmt = $db->prepare('SELECT nombre_completo, email FROM usuarios WHERE id = ? LIMIT 1');
                    $uStmt->bindParam(1, $row['usuario_id']);
                    $uStmt->execute();
                    if ($uStmt->rowCount() > 0) {
                        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                        if (!empty($uRow['nombre_completo'])) $usuario_nombre = $uRow['nombre_completo'];
                        elseif (!empty($uRow['email'])) $usuario_nombre = $uRow['email'];
                    }
                } catch (Exception $ue) { /* ignore */ }
            }
            $out[] = array(
                'id' => isset($row['id']) ? $row['id'] : null,
                'nombre' => isset($row['nombre']) ? $row['nombre'] : null,
                'tipo' => isset($row['tipo']) ? $row['tipo'] : null,
                'formato' => isset($row['formato']) ? $row['formato'] : null,
                'empresa_id' => isset($row['empresa_id']) ? $row['empresa_id'] : null,
                'empresa_nombre' => isset($row['empresa_nombre']) ? $row['empresa_nombre'] : null,
                'usuario_id' => isset($row['usuario_id']) ? $row['usuario_id'] : null,
                'usuario_nombre' => $usuario_nombre,
                'fecha_generacion' => isset($row['fecha_generacion']) ? $row['fecha_generacion'] : null,
                'url' => isset($row['url']) ? $row['url'] : null
            );
        }
        echo json_encode($out);
        http_response_code(200);
        exit(0);
    }
} catch (PDOException $e) {
    // If the reportes table doesn't exist in this DB, return empty list instead of raw SQL error
    if (strpos($e->getMessage(), 'Base table or view not found') !== false) {
        http_response_code(200);
        echo json_encode([]);
        exit(0);
    }
    http_response_code(500);
    echo json_encode(array('error' => true, 'message' => 'Database error'));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => true, 'message' => 'Server error'));
}

?>