<?php
require 'sunat.php';

header('Content-Type: application/json');

try {
    $ruc = $_GET['ruc'] ?? '';
    echo json_encode([
        'success' => true,
        'data' => validarRuc($ruc)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
