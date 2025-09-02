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

if (!empty($data->nombre)) {
    $query = "INSERT INTO empresas SET nombre=:nombre, locacion=:locacion, taladro=:taladro, ubicacion=:ubicacion, oit=:oit, activo=:activo";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(":nombre", htmlspecialchars(strip_tags($data->nombre)));
    $stmt->bindValue(":locacion", htmlspecialchars(strip_tags($data->locacion)));
    $stmt->bindValue(":taladro", htmlspecialchars(strip_tags($data->taladro)));
    $stmt->bindValue(":ubicacion", htmlspecialchars(strip_tags($data->ubicacion)));
    $stmt->bindValue(":oit", htmlspecialchars(strip_tags($data->oit)));
    $stmt->bindValue(":activo", htmlspecialchars(strip_tags($data->activo)));
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Empresa creada correctamente.", "id" => $db->lastInsertId()));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear la empresa."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere al menos un nombre."));
}
?>