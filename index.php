<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/sunat.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    // 🔐 Token simple (opcional pero recomendado)
    /*
    if (($_GET['token'] ?? '') !== 'MI_TOKEN_SECRETO') {
        http_response_code(403);
        throw new Exception('Acceso no autorizado');
    }
    */

    $ruc = $_GET['ruc'] ?? '';

    if ($ruc === '') {
        throw new Exception('Falta el parámetro ruc');
    }

    $data = validarRuc($ruc);

    echo json_encode([
        'success' => true,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
