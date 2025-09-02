<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Determinar si se solicita una herramienta específica o todas
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Leer una herramienta específica
    $query = "SELECT h.*, e.nombre as empresa_nombre FROM herramientas h LEFT JOIN empresas e ON h.empresa_id = e.id WHERE h.id = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);
        
        $herramienta = array(
            "id" => $id,
            "nombre" => $nombre,
            "empresa_id" => $empresa_id,
            "empresa_nombre" => $empresa_nombre,
            "tipo" => $tipo,
            "estado" => $estado,
            "ultimaInspeccion" => $ultimaInspeccion
        );
        
        http_response_code(200);
        echo json_encode($herramienta);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Herramienta no encontrada."));
    }
} else {
    // Leer todas las herramientas
    $query = "SELECT h.*, e.nombre as empresa_nombre FROM herramientas h LEFT JOIN empresas e ON h.empresa_id = e.id";
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
}
?>