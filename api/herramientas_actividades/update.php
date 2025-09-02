<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $query = "UPDATE herramientas_actividad SET herramienta_id=:herramienta_id, actividad_id=:actividad_id WHERE id=:id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":id", $data->id);
    $stmt->bindParam(":herramienta_id", htmlspecialchars(strip_tags($data->herramienta_id)));
    $stmt->bindParam(":actividad_id", htmlspecialchars(strip_tags($data->actividad_id)));
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Relación herramienta-actividad actualizada correctamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo actualizar la relación herramienta-actividad."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID de relación herramienta-actividad no proporcionado."));
}
?>