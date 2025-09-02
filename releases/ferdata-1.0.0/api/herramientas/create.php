<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

 $data = json_decode(file_get_contents("php://input"));

// Fallback: if no JSON body, try to populate from form-encoded POST
if ($data === null || $data === false) {
    if (!empty($_POST)) {
        $data = new stdClass();
        foreach ($_POST as $k => $v) {
            $data->{$k} = $v;
        }
    }
}

if (!empty($data->nombre) && !empty($data->empresa_id)) {
    $query = "INSERT INTO herramientas SET nombre=:nombre, empresa_id=:empresa_id, tipo=:tipo, estado=:estado, ultimaInspeccion=:ultimaInspeccion";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(":nombre", htmlspecialchars(strip_tags($data->nombre)));
    $stmt->bindValue(":empresa_id", htmlspecialchars(strip_tags($data->empresa_id)));
    $stmt->bindValue(":tipo", htmlspecialchars(strip_tags($data->tipo)));
    $stmt->bindValue(":estado", htmlspecialchars(strip_tags($data->estado)));
    $stmt->bindValue(":ultimaInspeccion", htmlspecialchars(strip_tags($data->ultimaInspeccion)));
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Herramienta creada correctamente.", "id" => $db->lastInsertId()));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear la herramienta."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere nombre e ID de empresa."));
}
?>