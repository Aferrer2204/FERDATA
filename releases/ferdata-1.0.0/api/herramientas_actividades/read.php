<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Determinar si se solicita una relación específica o todas
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Leer una relación específica
    $query = "SELECT ha.*, h.nombre as herramienta_nombre, a.nombre as actividad_nombre 
              FROM herramientas_actividad ha 
              LEFT JOIN herramientas h ON ha.herramienta_id = h.id 
              LEFT JOIN actividades a ON ha.actividad_id = a.id 
              WHERE ha.id = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);
        
        $herramienta_actividad = array(
            "id" => $id,
            "herramienta_id" => $herramienta_id,
            "herramienta_nombre" => $herramienta_nombre,
            "actividad_id" => $actividad_id,
            "actividad_nombre" => $actividad_nombre
        );
        
        http_response_code(200);
        echo json_encode($herramienta_actividad);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Relación herramienta-actividad no encontrada."));
    }
} else {
    // Leer todas las relaciones
    $query = "SELECT ha.*, h.nombre as herramienta_nombre, a.nombre as actividad_nombre 
              FROM herramientas_actividad ha 
              LEFT JOIN herramientas h ON ha.herramienta_id = h.id 
              LEFT JOIN actividades a ON ha.actividad_id = a.id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $herramientas_actividad_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $herramienta_actividad_item = array(
                "id" => $id,
                "herramienta_id" => $herramienta_id,
                "herramienta_nombre" => $herramienta_nombre,
                "actividad_id" => $actividad_id,
                "actividad_nombre" => $actividad_nombre
            );
            array_push($herramientas_actividad_arr, $herramienta_actividad_item);
        }
        
        http_response_code(200);
        echo json_encode($herramientas_actividad_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No se encontraron relaciones herramienta-actividad."));
    }
}
?>