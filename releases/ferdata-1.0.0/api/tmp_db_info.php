<?php
header('Content-Type: application/json; charset=UTF-8');

try {
    include_once __DIR__ . '/../backend/config/database.php';
    $dbObj = new Database();
    $db = $dbObj->getConnection();
    if (!$db) throw new Exception('No DB connection');

    $tables = ['actividades','usuarios'];
    $out = [];
    foreach ($tables as $t) {
        try {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$t`");
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $out[$t] = $cols;
        } catch (Exception $e) {
            $out[$t] = ['error' => $e->getMessage()];
        }
    }
    echo json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
