<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input']);
    exit(0);
}

try {
    $query = "INSERT INTO reportes (nombre, tipo, formato, empresa_id, usuario_id, fecha_generacion, url, parametros, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $input['nombre'] ?? null,
        $input['tipo'] ?? null,
        $input['formato'] ?? null,
        $input['empresa_id'] ?? null,
        $input['usuario_id'] ?? null,
        $input['fecha_generacion'] ?? null,
        $input['url'] ?? null,
        isset($input['parametros']) ? json_encode($input['parametros']) : null
    ]);
    http_response_code(201);
    echo json_encode(['id' => $db->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

?>
