<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/sunat.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $ruc = $_GET['ruc'] ?? '';
    if ($ruc === '') {
        throw new Exception('Falta el parámetro ruc');
    }

    echo json_encode([
        'success' => true,
        'data' => validarRuc($ruc)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
