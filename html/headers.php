<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Get all HTTP headers
$headers = array();

// Method 1: Using getallheaders() if available
if (function_exists('getallheaders')) {
    $headers = getallheaders();
} else {
    // Method 2: Parse $_SERVER array for headers
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
    }
    
    // Add some common headers that might not have HTTP_ prefix
    if (isset($_SERVER['CONTENT_TYPE'])) {
        $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
    }
    if (isset($_SERVER['CONTENT_LENGTH'])) {
        $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
    }
}

// Add additional server information
$serverInfo = array(
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
    'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'REMOTE_PORT' => $_SERVER['REMOTE_PORT'] ?? 'Unknown',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
    'HTTPS' => isset($_SERVER['HTTPS']) ? 'Yes' : 'No',
    'REQUEST_TIME' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time())
);

// Return the data as JSON
echo json_encode(array(
    'headers' => $headers,
    'server_info' => $serverInfo,
    'timestamp' => date('Y-m-d H:i:s')
), JSON_PRETTY_PRINT);
?>
