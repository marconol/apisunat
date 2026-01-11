<?php

/**
 * Consulta RUC en SUNAT (scraping)
 * Funciona en Coolify + PHP + Apache
 */

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
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);

    $html = curl_exec($ch);

    if ($html === false) {
        throw new Exception('Error conectando a SUNAT');
    }

    curl_close($ch);

    return parsearSunat($html);
}

function parsearSunat(string $html): array
{
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xp = new DOMXPath($dom);

    $data = [
        'razon_social' => '',
        'estado'       => '',
        'condicion'    => '',
        'direccion'    => '',
    ];

    // Recorremos todas las filas de tablas
    $rows = $xp->query("//tr");

    foreach ($rows as $row) {
        $cells = $row->getElementsByTagName('td');
        if ($cells->length < 2) continue;

        $label = strtoupper(trim(preg_replace('/\s+/', ' ', $cells->item(0)->textContent)));
        $value = trim(preg_replace('/\s+/', ' ', $cells->item(1)->textContent));

        if (str_contains($label, 'RAZÓN SOCIAL')) {
            $data['razon_social'] = $value;
        }
        if (str_contains($label, 'ESTADO')) {
            $data['estado'] = $value;
        }
        if (str_contains($label, 'CONDICIÓN')) {
            $data['condicion'] = $value;
        }
        if (str_contains($label, 'DOMICILIO')) {
            $data['direccion'] = $value;
        }
    }

    return $data;
}
