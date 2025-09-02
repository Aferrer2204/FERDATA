    <?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener todas las inspecciones con información relacionada
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
        $inspeccion_item = array(
            "id" => $id,
            "empresa_id" => $empresa_id,
            "empresa_nombre" => $empresa_nombre,
            "herramienta_id" => $herramienta_id,
            "herramienta_nombre" => $herramienta_nombre,
            "fecha" => $fecha,
            "resultado" => $resultado,
            "inspector" => $inspector,
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
?>