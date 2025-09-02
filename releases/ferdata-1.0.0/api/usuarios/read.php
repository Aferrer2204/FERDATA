<?php
// CORS headers - allow requests from dev server (and others)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // return only the headers and not the content
    http_response_code(200);
    exit(0);
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Helper: normalize activo value from possible legacy column names and formats
function normalize_activo(array $row) {
    $candidates = ['activo','active','estado','status','activo_usuario','estado_usuario','is_active'];
    foreach ($candidates as $c) {
        if (array_key_exists($c, $row)) {
            $v = $row[$c];
            if ($v === null) return null;
            // normalize common representations
            if (is_numeric($v)) return intval($v) === 1 ? 1 : 0;
            $s = strtolower(trim((string)$v));
            if ($s === '1' || $s === 'true' || $s === 't' || $s === 'si' || $s === 'sí' || $s === 'yes') return 1;
            if ($s === '0' || $s === 'false' || $s === 'f' || $s === 'no') return 0;
            // fallback: if non-empty string assume active
            return $s === '' ? 0 : 1;
        }
    }
    return null;
}

// Determinar si se solicita un usuario específico o todos
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    // Leer un usuario específico (defensivo)
    $query = "SELECT * FROM usuarios WHERE id = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $display = null;
        if (isset($row['nombre_completo']) && $row['nombre_completo']) $display = $row['nombre_completo'];
        elseif (isset($row['nombre']) && $row['nombre']) $display = $row['nombre'];

        $usuario = array(
            "id" => isset($row['id']) ? $row['id'] : null,
            "nombre" => $display,
            "email" => isset($row['email']) ? $row['email'] : null,
            "rol" => isset($row['rol']) ? $row['rol'] : null,
            "activo" => normalize_activo($row),
            "fecha_creacion" => isset($row['fecha_creacion']) ? $row['fecha_creacion'] : null
        );

        http_response_code(200);
        echo json_encode($usuario);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Usuario no encontrado."));
    }
} else {
    // Leer todos los usuarios
    $query = "SELECT * FROM usuarios";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) > 0) {
        $usuarios_arr = array();

        foreach ($rows as $row) {
            $display = null;
            if (isset($row['nombre_completo']) && $row['nombre_completo']) $display = $row['nombre_completo'];
            elseif (isset($row['nombre']) && $row['nombre']) $display = $row['nombre'];

            $usuario_item = array(
                "id" => isset($row['id']) ? $row['id'] : null,
                "nombre" => $display,
                "email" => isset($row['email']) ? $row['email'] : null,
                "rol" => isset($row['rol']) ? $row['rol'] : null,
                "activo" => normalize_activo($row),
                "fecha_creacion" => isset($row['fecha_creacion']) ? $row['fecha_creacion'] : null
            );
            array_push($usuarios_arr, $usuario_item);
        }

        http_response_code(200);
        echo json_encode($usuarios_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No se encontraron usuarios."));
    }
}
?>