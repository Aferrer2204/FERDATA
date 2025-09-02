<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener estadísticas
// Build counts defensively: some tables may not exist in legacy DBs
$counts = [
    'empresas' => 0,
    'herramientas' => 0,
    'inspecciones' => 0,
    'reportes' => 0
];

$tables = [
    'empresas' => 'empresas',
    'herramientas' => 'herramientas',
    'inspecciones' => 'inspecciones',
    'reportes' => 'reportes'
];

foreach ($tables as $key => $table) {
    try {
        $q = $db->prepare("SELECT COUNT(*) AS cnt FROM `" . $table . "`");
        $q->execute();
        $r = $q->fetch(PDO::FETCH_ASSOC);
        $counts[$key] = isset($r['cnt']) ? (int)$r['cnt'] : 0;
    } catch (PDOException $e) {
        // Table missing or other error — keep 0 and continue
        error_log("stats: could not count table {$table}: " . $e->getMessage());
        $counts[$key] = 0;
    }
}

http_response_code(200);
echo json_encode($counts);
?> 