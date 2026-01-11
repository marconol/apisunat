<?php

function validarRuc(string $ruc): array
{
    if (!preg_match('/^\d{11}$/', $ruc)) {
        throw new Exception('RUC inválido');
    }

    $url = 'https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias';

    $postData = http_build_query([
        'accion' => 'consPorRuc',
        'nroRuc' => $ruc,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $html = curl_exec($ch);

    if ($html === false) {
        throw new Exception('Error SUNAT');
    }

    curl_close($ch);

    return parsear($html);
}

function parsear(string $html): array
{
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xp = new DOMXPath($dom);

    $get = fn($txt) =>
        trim($xp->query("//td[contains(text(),'$txt')]/following-sibling::td")
            ->item(0)?->nodeValue ?? '');

    return [
        'razon_social' => $get('Razón Social'),
        'estado'       => $get('Estado del Contribuyente'),
        'condicion'    => $get('Condición del Contribuyente'),
        'direccion'    => $get('Dirección'),
    ];
}

