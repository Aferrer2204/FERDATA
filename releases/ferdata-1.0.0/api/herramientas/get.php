<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener todas las herramientas con información de empresa
$query = "SELECT h.*, e.nombre as empresa_nombre 
          FROM herramientas h 
          LEFT JOIN empresas e ON h.empresa_id = e.id";
$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $herramientas_arr = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $herramienta_item = array(
            "id" => $id,
            "nombre" => $nombre,
            "empresa_id" => $empresa_id,
            "empresa_nombre" => $empresa_nombre,
            "tipo" => $tipo,
            "estado" => $estado,
            "ultimaInspeccion" => $ultimaInspeccion
        );
        array_push($herramientas_arr, $herramienta_item);
    }
    
    http_response_code(200);
    echo json_encode($herramientas_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No se encontraron herramientas."));
}
?>