<?php
header('Content-Type: application/json; charset=UTF-8');

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
    exit;
}

if (!isset($_GET['lofy']) || empty($_GET['lofy'])) {
    sendError('Parâmetro "lofy" é obrigatório');
}

$bin = trim($_GET['lofy']);
if (!preg_match('/^\d{6,8}$/', $bin)) {
    sendError('INVALID_BIN. coloca a prr de uma bin nessa kraia mermao');
}

$csvFile = 'bins.csv';

if (!file_exists($csvFile)) {
    sendError('INVALID_FILE_BINS', 500);
}

$found = false;
$response = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    $header = fgetcsv($handle, 1000, ',');
    if ($header === false || empty($header)) {
        fclose($handle);
        sendError('INVALID_DB', 500);
    };

    $columns = [
        'bin' => array_search('BIN', $header),
        'brand' => array_search('Brand', $header),
        'type' => array_search('Type', $header),
        'category' => array_search('Category', $header),
        'issuer' => array_search('Issuer', $header),
        'isoCode2' => array_search('isoCode2', $header),
        'CountryName' => array_search('CountryName', $header)
    ];

    foreach ($columns as $key => $index) {
        if ($index === false) {
            fclose($handle);
            sendError("INVALID_COLUMN. $key", 500);
        }
    }

    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        if (count($data) < count($columns)) {
            continue;
        }
        if ($data[$columns['bin']] === $bin) {
            $response = [
                'Bin' => $data[$columns['bin']] ?: '------',
                'Bandeira' => $data[$columns['brand']] ?: '------',
                'Tipo' => $data[$columns['type']] ?: '------',
                'Nivel' => $data[$columns['category']] ?: '------',
                'Banco' => $data[$columns['issuer']] ?: '------',
                'Pais' => $data[$columns['CountryName']] ?: '------',
                'ISO' => $data[$columns['isoCode2']] ?: '------'
            ];
            $found = true;
            break;
        }
    }
    fclose($handle);
}

if (!$found) {
    sendError('INVALID_BIN. coloca a prr de uma bin valida mermao');
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>