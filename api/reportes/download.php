<?php
header("Access-Control-Allow-Origin: *");
// Try to serve a URL or file for a given report id. This is a lightweight compatibility endpoint.
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing id']);
    exit(0);
}

try {
    $stmt = $db->prepare('SELECT url FROM reportes WHERE id = ? LIMIT 1');
    $stmt->bindParam(1, $id);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(['message' => 'Reporte no encontrado']);
        exit(0);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $url = $row['url'];
    if (!$url) {
        http_response_code(404);
        echo json_encode(['message' => 'URL del reporte no disponible']);
        exit(0);
    }
    // If URL is a file path under the project, attempt to serve it
    if (strpos($url, 'http') !== 0 && file_exists(__DIR__ . '/../../' . ltrim($url, '/'))) {
        $filePath = realpath(__DIR__ . '/../../' . ltrim($url, '/'));
        // Basic security: ensure file is inside project
        if (strpos($filePath, realpath(__DIR__ . '/../../')) !== 0) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied']);
            exit(0);
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit(0);
    }

    // Otherwise return JSON with URL for client to open
    echo json_encode(['url' => $url]);
    exit(0);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

?>
