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

// Helper: get Authorization header in PHP (works with various server vars)
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// Try to extract user id and name from JWT Bearer token or X-User-Id header
$currentUserId = null;
$currentUserName = null;
$authHeader = getAuthorizationHeader();
if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
    // naive decode of JWT payload (no signature verification) to extract id
    $parts = explode('.', $jwt);
    if (count($parts) === 3) {
        $payload = $parts[1];
        $payload .= str_repeat('=', 3 - (strlen($payload) % 4));
        $decoded = base64_decode(strtr($payload, '-_', '+/'));
        if ($decoded) {
            $obj = json_decode($decoded, true);
            if ($obj && isset($obj['id'])) {
                $currentUserId = $obj['id'];
            }
        }
    }
}
// X-User-Id header fallback (useful for local testing)
if (!$currentUserId && isset($_SERVER['HTTP_X_USER_ID'])) {
    $currentUserId = $_SERVER['HTTP_X_USER_ID'];
}

// If we have a user id, try to load the user's display name
if ($currentUserId) {
    try {
        $uStmt = $db->prepare("SELECT id, nombre_completo, nombre FROM usuarios WHERE id = ? LIMIT 1");
        $uStmt->bindParam(1, $currentUserId);
        $uStmt->execute();
        if ($uStmt->rowCount() > 0) {
            $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($uRow['nombre_completo'])) $currentUserName = $uRow['nombre_completo'];
            elseif (!empty($uRow['nombre'])) $currentUserName = $uRow['nombre'];
        }
    } catch (Exception $e) {
        // ignore
    }
}

try {
    // Inspect table columns to adapt to different schemas
    $colsStmt = $db->prepare("SHOW COLUMNS FROM actividades");
    $colsStmt->execute();
    $cols = array_map(function($r){ return $r['Field']; }, $colsStmt->fetchAll(PDO::FETCH_ASSOC));

    $safeFecha = isset($data->fecha) ? htmlspecialchars(strip_tags($data->fecha)) : date('Y-m-d');

    // Schema A: columns nombre + descripcion (newer)
    if (in_array('nombre', $cols) && in_array('descripcion', $cols)) {
        $nombreInput = isset($data->nombre) && strlen(trim($data->nombre)) ? $data->nombre : (isset($data->tipo) ? $data->tipo : null);
        $descripcionInput = isset($data->descripcion) ? $data->descripcion : null;

        if (empty($nombreInput) || empty($descripcionInput)) {
            http_response_code(400);
            echo json_encode(array("message" => "Datos incompletos. Se requiere nombre y descripción."));
            exit;
        }

        $query = "INSERT INTO actividades (nombre, descripcion, fecha, usuario_id, avance) VALUES (:nombre, :descripcion, :fecha, :usuario_id, :avance)";
        $stmt = $db->prepare($query);
        $safeNombre = htmlspecialchars(strip_tags($nombreInput));
        $safeDescripcion = htmlspecialchars(strip_tags($descripcionInput));
    // prefer explicit usuario_id in payload, otherwise use detected current user id
    $safeUsuarioId = isset($data->usuario_id) ? htmlspecialchars(strip_tags($data->usuario_id)) : ($currentUserId ? htmlspecialchars(strip_tags($currentUserId)) : null);
        $avanceVal = isset($data->avance) ? intval($data->avance) : 0;

        $stmt->bindParam(":nombre", $safeNombre);
        $stmt->bindParam(":descripcion", $safeDescripcion);
        $stmt->bindParam(":fecha", $safeFecha);
        $stmt->bindParam(":usuario_id", $safeUsuarioId);
        $stmt->bindParam(":avance", $avanceVal);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Actividad creada correctamente.", "id" => $db->lastInsertId()));
            exit;
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo crear la actividad."));
            exit;
        }
    }

    // Schema B: legacy columns like fecha, cliente_id, observaciones, contacto, lugar, locacion, inspector
    if (in_array('observaciones', $cols) || in_array('empresa', $cols)) {
        // adapt to live schema: use 'empresa' and 'usuario' (string) columns
        $empresaId = isset($data->empresa_id) ? $data->empresa_id : null;
        $observ = isset($data->descripcion) ? $data->descripcion : (isset($data->observaciones) ? $data->observaciones : null);

        $query = "INSERT INTO actividades (fecha, empresa, contacto, locacion, usuario, observaciones, avance) VALUES (:fecha, :empresa, :contacto, :locacion, :usuario, :observaciones, :avance)";
        $stmt = $db->prepare($query);
        $contacto = isset($data->contacto) ? htmlspecialchars(strip_tags($data->contacto)) : null;
        $locacion = isset($data->locacion) ? htmlspecialchars(strip_tags($data->locacion)) : null;
        // If usuario provided use it; else use detected current user's name when available
        $usuarioVal = isset($data->usuario) ? htmlspecialchars(strip_tags($data->usuario)) : ($currentUserName ? htmlspecialchars(strip_tags($currentUserName)) : null);
        $safeObserv = $observ ? htmlspecialchars(strip_tags($observ)) : null;
        $avanceVal = isset($data->avance) ? intval($data->avance) : (isset($data->avance) ? $data->avance : null);

        $stmt->bindParam(":fecha", $safeFecha);
        $stmt->bindParam(":empresa", $empresaId);
        $stmt->bindParam(":contacto", $contacto);
        $stmt->bindParam(":locacion", $locacion);
        $stmt->bindParam(":usuario", $usuarioVal);
        $stmt->bindParam(":observaciones", $safeObserv);
        $stmt->bindParam(":avance", $avanceVal);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "Actividad creada correctamente (legacy schema).", "id" => $db->lastInsertId()));
            exit;
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo crear la actividad (legacy schema)."));
            exit;
        }
    }

    // If we reach here, schema is unknown
    http_response_code(500);
    echo json_encode(array("error" => true, "message" => "Estructura de tabla 'actividades' no reconocida."));

} catch (Exception $e) {
    // Log error for debugging to deployed logs folder
    $logPath = __DIR__ . '/../../logs/create_error.log';
    $msg = date('c') . " - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
    @file_put_contents($logPath, $msg, FILE_APPEND);
    http_response_code(500);
    // Return the exception message temporarily to help debugging
    echo json_encode(array("error" => true, "message" => $e->getMessage()));
}
?>