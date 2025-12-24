<?php
header('Content-Type: application/json');

function get_all_headers() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
}

$headers = get_all_headers();
$client_ip = $_SERVER['REMOTE_ADDR'];
$proxy_ip = isset($headers['X-Forwarded-For']) ? $headers['X-Forwarded-For'] : null;
$is_zscaler = false;

foreach ($headers as $key => $value) {
    if (stripos($key, 'zscaler') !== false) {
        $is_zscaler = true;
        break;
    }
}

$response = [
    'ip_address' => $client_ip,
    'proxy_ip' => $proxy_ip,
    'is_zscaler' => $is_zscaler,
    'headers' => $headers,
    'server_info' => $_SERVER,
    'health_check' => [
        'status' => 'ok',
        'timestamp' => date(DateTime::ISO8601)
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);

