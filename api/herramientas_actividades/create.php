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

if (!empty($data->herramienta_id) && !empty($data->actividad_id)) {
    $query = "INSERT INTO herramientas_actividad SET herramienta_id=:herramienta_id, actividad_id=:actividad_id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":herramienta_id", htmlspecialchars(strip_tags($data->herramienta_id)));
    $stmt->bindParam(":actividad_id", htmlspecialchars(strip_tags($data->actividad_id)));
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Relación herramienta-actividad creada correctamente.", "id" => $db->lastInsertId()));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear la relación herramienta-actividad."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere ID de herramienta y ID de actividad."));
}
?>