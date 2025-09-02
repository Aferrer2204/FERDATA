<?php
// Lightweight compatibility endpoint: return an empty list when reportes table is not present
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Return empty array without touching the DB to avoid SQL errors on systems without the reportes table
http_response_code(200);
echo json_encode([]);

?>
