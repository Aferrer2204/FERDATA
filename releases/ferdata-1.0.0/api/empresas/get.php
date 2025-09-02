<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener todas las empresas
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
?>