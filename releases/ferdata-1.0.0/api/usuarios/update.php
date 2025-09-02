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
    // Construir la consulta dinámicamente
    $fields = array();
    if (!empty($data->nombre)) {
        $fields[] = "nombre=:nombre";
    }
    if (!empty($data->email)) {
        $fields[] = "email=:email";
    }
    if (!empty($data->password)) {
        $fields[] = "password=:password";
    }
    if (isset($data->rol)) {
        $fields[] = "rol=:rol";
    }
    if (isset($data->activo)) {
        $fields[] = "activo=:activo";
    }
    
    if (count($fields) > 0) {
        $query = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id=:id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":id", $data->id);
        if (!empty($data->nombre)) {
            $stmt->bindParam(":nombre", htmlspecialchars(strip_tags($data->nombre)));
        }
        if (!empty($data->email)) {
            $stmt->bindParam(":email", htmlspecialchars(strip_tags($data->email)));
        }
        if (!empty($data->password)) {
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            $stmt->bindParam(":password", $password_hash);
        }
        if (isset($data->rol)) {
            $stmt->bindParam(":rol", htmlspecialchars(strip_tags($data->rol)));
        }
        if (isset($data->activo)) {
            $stmt->bindParam(":activo", htmlspecialchars(strip_tags($data->activo)));
        }
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(array("message" => "Usuario actualizado correctamente."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo actualizar el usuario."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "No se proporcionaron datos para actualizar."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "ID de usuario no proporcionado."));
}
?>