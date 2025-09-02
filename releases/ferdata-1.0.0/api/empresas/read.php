<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Determinar si se solicita una empresa específica o todas
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Leer una empresa específica
    $query = "SELECT * FROM empresas WHERE id = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);
        
        $empresa = array(
            "id" => $id,
            "nombre" => $nombre,
            "locacion" => $locacion,
            "taladro" => $taladro,
            "ubicacion" => $ubicacion,
            "oit" => $oit,
            "activo" => $activo
        );
        
        http_response_code(200);
        echo json_encode($empresa);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Empresa no encontrada."));
    }
} else {
    // Leer todas las empresas
    $query = "SELECT * FROM empresas";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $num = $stmt->rowCount();
    
    if ($num > 0) {
        $empresas_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $empresa_item = array(
                "id" => $id,
                "nombre" => $nombre,
                "locacion" => $locacion,
                "taladro" => $taladro,
                "ubicacion" => $ubicacion,
                "oit" => $oit,
                "activo" => $activo
            );
            array_push($empresas_arr, $empresa_item);
        }
        
        http_response_code(200);
        echo json_encode($empresas_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No se encontraron empresas."));
    }
}
?>