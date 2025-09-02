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
    $query = "UPDATE actividades SET nombre=:nombre, descripcion=:descripcion, fecha=:fecha, usuario_id=:usuario_id, avance=:avance WHERE id=:id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":id", $data->id);
    $stmt->bindParam(":nombre", htmlspecialchars(strip_tags($data->nombre)));
    $stmt->bindParam(":descripcion", htmlspecialchars(strip_tags($data->descripcion)));
    $stmt->bindParam(":fecha", htmlspecialchars(strip_tags($data->fecha)));
    $stmt->bindParam(":usuario_id", htmlspecialchars(strip_tags($data->usuario_id)));
    $avanceVal = isset($data->avance) ? intval($data->avance) : 0;
    $stmt->bindParam(":avance", $avanceVal);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Actividad actualizada correctamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo actualizar la actividad."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID de actividad no proporcionado."));
}
?>