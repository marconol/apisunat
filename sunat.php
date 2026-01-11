<?php

function validarRuc(string $ruc): array
{
    if (!preg_match('/^\d{11}$/', $ruc)) {
        throw new Exception('RUC inválido');
    }

    $cookie = tempnam(sys_get_temp_dir(), 'sunat_');

    // 1️⃣ Primer request (inicia sesión)
    $ch = curl_init('https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    curl_exec($ch);
    curl_close($ch);

    // 2️⃣ Segundo request (consulta real)
    $postData = http_build_query([
        'accion' => 'consPorRuc',
        'nroRuc' => $ruc,
    ]);

    $ch = curl_init('https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $html = curl_exec($ch);
    curl_close($ch);
    unlink($cookie);

    if (!$html || strlen($html) < 500) {
        throw new Exception('SUNAT no respondió correctamente');
    }

    return parsearSunatTexto($html);
}

function limpiar(string $text): string
{
    return trim(preg_replace('/\s+/', ' ', html_entity_decode($text)));
}

function extraer(string $html, string $label): string
{
    if (preg_match('/' . preg_quote($label, '/') . '\s*:\s*(.*?)</i', $html, $m)) {
        return limpiar($m[1]);
    }
    return '';
}

function parsearSunatTexto(string $html): array
{
    return [
        'razon_social' => extraer($html, 'Razón Social'),
        'estado'       => extraer($html, 'Estado del Contribuyente'),
        'condicion'    => extraer($html, 'Condición del Contribuyente'),
        'direccion'    => extraer($html, 'Domicilio Fiscal'),
    ];
}
