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

if (!empty($data->nombre) && !empty($data->email) && !empty($data->password)) {
    $query = "INSERT INTO usuarios SET nombre=:nombre, email=:email, password=:password, rol=:rol, activo=:activo";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":nombre", htmlspecialchars(strip_tags($data->nombre)));
    $stmt->bindParam(":email", htmlspecialchars(strip_tags($data->email)));
    
    // Encriptar la contraseña
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
    $stmt->bindParam(":password", $password_hash);
    
    $stmt->bindParam(":rol", htmlspecialchars(strip_tags($data->rol)));
    $stmt->bindParam(":activo", htmlspecialchars(strip_tags($data->activo)));
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Usuario creado correctamente.", "id" => $db->lastInsertId()));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "No se pudo crear el usuario."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos. Se requiere nombre, email y password."));
}
?>