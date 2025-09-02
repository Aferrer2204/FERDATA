<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Determinar si se solicita una actividad específica o todas
$id = isset($_GET['id']) ? $_GET['id'] : null;

try {
    if ($id) {
        // Leer una actividad específica
        // Avoid joining usuarios to prevent column mismatch on different schemas; return usuario_id and let frontend resolve name
        $query = "SELECT a.* FROM actividades a WHERE a.id = ? LIMIT 0,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Normalize fields: support both new (nombre/descripcion/avance) and legacy (observaciones)
            $descripcion = isset($row['descripcion']) ? $row['descripcion'] : (isset($row['observaciones']) ? $row['observaciones'] : null);
            $nombreVal = isset($row['nombre']) ? $row['nombre'] : null;
            $tipoVal = $nombreVal;
            // Try to resolve usuario_nombre from usuarios.nombre_completo (fallback to nombre)
            $usuario_nombre = null;
            if (isset($row['usuario_id']) && $row['usuario_id']) {
                try {
                    $uStmt = $db->prepare("SELECT id, nombre_completo, nombre FROM usuarios WHERE id = ? LIMIT 1");
                    $uStmt->bindParam(1, $row['usuario_id']);
                    $uStmt->execute();
                    if ($uStmt->rowCount() > 0) {
                        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                        if (isset($uRow['nombre_completo']) && $uRow['nombre_completo']) $usuario_nombre = $uRow['nombre_completo'];
                        elseif (isset($uRow['nombre']) && $uRow['nombre']) $usuario_nombre = $uRow['nombre'];
                    }
                } catch (Exception $ue) {
                    // ignore and leave usuario_nombre null
                }
            }

            $actividad = array(
                "id" => isset($row['id']) ? $row['id'] : $id,
                "nombre" => $nombreVal,
                "tipo" => $tipoVal,
                "descripcion" => $descripcion,
                "fecha" => isset($row['fecha']) ? $row['fecha'] : null,
                // Resolve empresa_nombre when possible
                "empresa_id" => isset($row['empresa_id']) ? $row['empresa_id'] : null,
                "empresa_nombre" => null,
                "usuario_id" => isset($row['usuario_id']) ? $row['usuario_id'] : null,
                "usuario_nombre" => $usuario_nombre,
                "avance" => isset($row['avance']) ? intval($row['avance']) : 0
            );

            // resolve empresa_nombre if empresa_id present
            if (!empty($actividad['empresa_id'])) {
                try {
                    $eStmt = $db->prepare('SELECT nombre FROM empresas WHERE id = ? LIMIT 1');
                    $eStmt->bindParam(1, $actividad['empresa_id']);
                    $eStmt->execute();
                    if ($eStmt->rowCount() > 0) {
                        $eRow = $eStmt->fetch(PDO::FETCH_ASSOC);
                        $actividad['empresa_nombre'] = isset($eRow['nombre']) ? $eRow['nombre'] : null;
                    }
                } catch (Exception $e) {}
            }

            http_response_code(200);
            echo json_encode($actividad);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Actividad no encontrada."));
        }
    } else {
        // Leer todas las actividades
    // Avoid join to usuarios to prevent schema mismatches
    $query = "SELECT a.* FROM actividades a";
        $stmt = $db->prepare($query);
        $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $actividades_arr = array();

            // Collect usuario_ids for batch lookup
            $usuarioIds = array();
            foreach ($rows as $r) {
                if (isset($r['usuario_id']) && $r['usuario_id']) $usuarioIds[] = $r['usuario_id'];
            }
            $userMap = array();
            if (count($usuarioIds) > 0) {
                // Unique ids
                $usuarioIds = array_values(array_unique($usuarioIds));
                // Build placeholders
                $placeholders = implode(',', array_fill(0, count($usuarioIds), '?'));
                try {
                    $uQuery = "SELECT id, nombre_completo, nombre FROM usuarios WHERE id IN ($placeholders)";
                    $uStmt = $db->prepare($uQuery);
                    foreach ($usuarioIds as $i => $uid) {
                        $uStmt->bindValue($i+1, $uid);
                    }
                    $uStmt->execute();
                    $uRows = $uStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($uRows as $ur) {
                        if (isset($ur['nombre_completo']) && $ur['nombre_completo']) $userMap[$ur['id']] = $ur['nombre_completo'];
                        elseif (isset($ur['nombre']) && $ur['nombre']) $userMap[$ur['id']] = $ur['nombre'];
                    }
                } catch (Exception $ue) {
                    // ignore failures and leave userMap empty
                }
            }

            foreach ($rows as $row) {
                $descripcion = isset($row['descripcion']) ? $row['descripcion'] : (isset($row['observaciones']) ? $row['observaciones'] : null);
                $nombreVal = isset($row['nombre']) ? $row['nombre'] : null;
                $usuario_nombre = null;
                if (isset($row['usuario_id']) && $row['usuario_id'] && isset($userMap[$row['usuario_id']])) {
                    $usuario_nombre = $userMap[$row['usuario_id']];
                }
                // Fallback: if still null, use 'inspector' or 'usuario' column when present (legacy schema)
                if (empty($usuario_nombre)) {
                    if (isset($row['inspector']) && $row['inspector']) {
                        $usuario_nombre = $row['inspector'];
                    } elseif (isset($row['usuario']) && $row['usuario']) {
                        $usuario_nombre = $row['usuario'];
                    }
                }
                $actividad_item = array(
                    "id" => isset($row['id']) ? $row['id'] : null,
                    "nombre" => $nombreVal,
                    "tipo" => $nombreVal,
                    "descripcion" => $descripcion,
                    "fecha" => isset($row['fecha']) ? $row['fecha'] : null,
                    "empresa_id" => isset($row['empresa_id']) ? $row['empresa_id'] : null,
                    "empresa_nombre" => null,
                    "usuario_id" => isset($row['usuario_id']) ? $row['usuario_id'] : null,
                    "usuario_nombre" => $usuario_nombre,
                    "avance" => isset($row['avance']) ? intval($row['avance']) : 0
                );
                if (!empty($actividad_item['empresa_id'])) {
                    try {
                        $eStmt = $db->prepare('SELECT nombre FROM empresas WHERE id = ? LIMIT 1');
                        $eStmt->bindParam(1, $actividad_item['empresa_id']);
                        $eStmt->execute();
                        if ($eStmt->rowCount() > 0) {
                            $eRow = $eStmt->fetch(PDO::FETCH_ASSOC);
                            $actividad_item['empresa_nombre'] = isset($eRow['nombre']) ? $eRow['nombre'] : null;
                        }
                    } catch (Exception $e) {}
                }
                array_push($actividades_arr, $actividad_item);
            }

            http_response_code(200);
            echo json_encode($actividades_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No se encontraron actividades."));
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("error" => true, "message" => $e->getMessage()));
}
?>