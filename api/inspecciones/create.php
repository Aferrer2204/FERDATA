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

if (!empty($data->empresa_id) && !empty($data->herramienta_id) && !empty($data->fecha)) {
    $query = "INSERT INTO inspecciones SET empresa_id=:empresa_id, herramienta_id=:herramienta_id, fecha=:fecha, resultado=:resultado, inspector=:inspector, observaciones=:observaciones";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(":empresa_id", htmlspecialchars(strip_tags($data->empresa_id)));
    $stmt->bindValue(":herramienta_id", htmlspecialchars(strip_tags($data->herramienta_id)));
    $stmt->bindValue(":fecha", htmlspecialchars(strip_tags($data->fecha)));
    $stmt->bindValue(":resultado", htmlspecialchars(strip_tags($data->resultado)));
    $stmt->bindValue(":inspector", htmlspecialchars(strip_tags($data->inspector)));
    $stmt->bindValue(":observaciones", htmlspecialchars(strip_tags($data->observaciones)));
    
    if ($stmt->execute()) {
        // Actualizar la última inspección de la herramienta
        $updateQuery = "UPDATE herramientas SET ultimaInspeccion = :fecha WHERE id = :herramienta_id";
        $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindValue(":fecha", htmlspecialchars(strip_tags($data->fecha)));
    $updateStmt->bindValue(":herramienta_id", htmlspecialchars(strip_tags($data->herramienta_id)));
        $updateStmt->execute();
        
        http_response_code(201);
        echo json_encode(array("message" => "Inspección creada correctamente.", "id" => $db->lastInsertId()));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear la inspección."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere empresa, herramienta y fecha."));
}
?>