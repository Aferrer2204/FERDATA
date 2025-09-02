<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Determinar si se solicita una inspección específica o todas
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Leer una inspección específica
    $query = "SELECT i.*, e.nombre as empresa_nombre, h.nombre as herramienta_nombre 
              FROM inspecciones i 
              LEFT JOIN empresas e ON i.empresa_id = e.id 
              LEFT JOIN herramientas h ON i.herramienta_id = h.id 
              WHERE i.id = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);
        
        // Normalize inspector name field for frontend (inspector_nombre)
        $inspector_nombre = null;
        if (isset($inspector) && $inspector) $inspector_nombre = $inspector;
        // try to resolve from usuarios table if value is an id
        if (is_numeric($inspector)) {
            try {
                $uStmt = $db->prepare('SELECT nombre_completo, nombre FROM usuarios WHERE id = ? LIMIT 1');
                $uStmt->bindParam(1, $inspector);
                $uStmt->execute();
                if ($uStmt->rowCount() > 0) {
                    $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                    if (!empty($uRow['nombre_completo'])) $inspector_nombre = $uRow['nombre_completo'];
                    elseif (!empty($uRow['nombre'])) $inspector_nombre = $uRow['nombre'];
                }
            } catch (Exception $e) { /* ignore */ }
        }

        $inspeccion = array(
            "id" => $id,
            "empresa_id" => $empresa_id,
            "empresa_nombre" => $empresa_nombre,
            "herramienta_id" => $herramienta_id,
            "herramienta_nombre" => $herramienta_nombre,
            "fecha" => $fecha,
            "resultado" => $resultado,
            "inspector" => $inspector,
            "inspector_nombre" => $inspector_nombre,
            "observaciones" => $observaciones
        );
        
        http_response_code(200);
        echo json_encode($inspeccion);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Inspección no encontrada."));
    }
} else {
    // Leer todas las inspecciones
    $query = "SELECT i.*, e.nombre as empresa_nombre, h.nombre as herramienta_nombre 
              FROM inspecciones i 
              LEFT JOIN empresas e ON i.empresa_id = e.id 
              LEFT JOIN herramientas h ON i.herramienta_id = h.id 
              ORDER BY i.fecha DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $inspecciones_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            // Normalize inspector_nombre like single endpoint
            $inspector_nombre = null;
            if (isset($inspector) && $inspector) $inspector_nombre = $inspector;
            if (is_numeric($inspector)) {
                try {
                    $uStmt = $db->prepare('SELECT nombre_completo, nombre FROM usuarios WHERE id = ? LIMIT 1');
                    $uStmt->bindParam(1, $inspector);
                    $uStmt->execute();
                    if ($uStmt->rowCount() > 0) {
                        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
                        if (!empty($uRow['nombre_completo'])) $inspector_nombre = $uRow['nombre_completo'];
                        elseif (!empty($uRow['nombre'])) $inspector_nombre = $uRow['nombre'];
                    }
                } catch (Exception $e) { /* ignore */ }
            }

            $inspeccion_item = array(
                "id" => $id,
                "empresa_id" => $empresa_id,
                "empresa_nombre" => $empresa_nombre,
                "herramienta_id" => $herramienta_id,
                "herramienta_nombre" => $herramienta_nombre,
                "fecha" => $fecha,
                "resultado" => $resultado,
                "inspector" => $inspector,
                "inspector_nombre" => $inspector_nombre,
                "observaciones" => $observaciones
            );
            array_push($inspecciones_arr, $inspeccion_item);
        }
        
        http_response_code(200);
        echo json_encode($inspecciones_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No se encontraron inspecciones."));
    }
}
?>