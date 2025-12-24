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

// Enhanced proxy detection logic
function detectProxyConnection($headers, $serverVars) {
    $proxyHeaders = [
        'x-forwarded-for',
        'x-real-ip', 
        'x-forwarded-proto',
        'x-forwarded-host',
        'via',
        'x-proxy-connection',
        'x-forwarded-server',
        'x-cluster-client-ip',
        'forwarded',
        'cf-ray',
        'cf-connecting-ip',
        'x-coming-from',
        'x-forwarded-client-cert'
    ];
    
    $zscalerHeaders = [
        'z-forwarded-for',
        'x-zscaler-client-ip',
        'x-zscaler-trace',
        'x-zscaler-proxy'
    ];
    
    $deereZscalerHeaders = [
        'deere-zscaler',
        'x-deere-zscaler'
    ];
    
    $detectedHeaders = [];
    $isProxy = false;
    $isZscaler = false;
    $isDeereZscaler = false;
    
    // Check headers (case-insensitive)
    foreach ($headers as $headerName => $headerValue) {
        $lowerHeaderName = strtolower($headerName);
        $lowerHeaderValue = strtolower($headerValue);
        
        // Check for general proxy headers
        if (in_array($lowerHeaderName, $proxyHeaders)) {
            $isProxy = true;
            $detectedHeaders[] = "$headerName: $headerValue";
        }
        
        // Check for Zscaler-specific headers
        if (in_array($lowerHeaderName, $zscalerHeaders)) {
            $isProxy = true;
            $isZscaler = true;
            $detectedHeaders[] = "$headerName: $headerValue (Zscaler)";
        }
        
        // Check for Deere-specific Zscaler headers
        foreach ($deereZscalerHeaders as $deereHeader) {
            if (strpos($lowerHeaderName, $deereHeader) !== false || strpos($lowerHeaderValue, $deereHeader) !== false) {
                $isProxy = true;
                $isZscaler = true;
                $isDeereZscaler = true;
                $detectedHeaders[] = "$headerName: $headerValue (Deere Zscaler)";
            }
        }
    }
    
    // Check for proxy chain in X-Forwarded-For
    $xForwardedFor = null;
    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'x-forwarded-for') {
            $xForwardedFor = $value;
            break;
        }
    }
    
    $hasProxyChain = $xForwardedFor && strpos($xForwardedFor, ',') !== false;
    if ($hasProxyChain && !$isProxy) {
        $isProxy = true;
        $detectedHeaders[] = "X-Forwarded-For proxy chain detected: $xForwardedFor";
    }
    
    return [
        'isProxy' => $isProxy,
        'isZscaler' => $isZscaler,
        'isDeereZscaler' => $isDeereZscaler,
        'detectedHeaders' => $detectedHeaders,
        'proxyChain' => $xForwardedFor
    ];
}

// Detect proxy connection
$proxyInfo = detectProxyConnection($headers, $_SERVER);

// Extract forwarding information
$forwardingInfo = [];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$gatewayIP = $clientIP;

// Extract various IP-related headers
$forwardingHeaders = [
    'x-forwarded-for' => 'X-Forwarded-For',
    'x-real-ip' => 'X-Real-IP',
    'x-forwarded-proto' => 'X-Forwarded-Proto',
    'x-forwarded-host' => 'X-Forwarded-Host',
    'x-forwarded-port' => 'X-Forwarded-Port',
    'cf-connecting-ip' => 'CF-Connecting-IP',
    'x-cluster-client-ip' => 'X-Cluster-Client-IP'
];

foreach ($headers as $name => $value) {
    $lowerName = strtolower($name);
    if (array_key_exists($lowerName, $forwardingHeaders)) {
        $forwardingInfo[$forwardingHeaders[$lowerName]] = $value;
        
        // Use X-Forwarded-For or similar as gateway IP if available
        if ($lowerName === 'x-forwarded-for') {
            // If there's a comma, take the first IP (original client)
            $ips = array_map('trim', explode(',', $value));
            $gatewayIP = $ips[0];
        } elseif (in_array($lowerName, ['x-real-ip', 'cf-connecting-ip', 'x-cluster-client-ip']) && $gatewayIP === $clientIP) {
            $gatewayIP = $value;
        }
    }
}

// Build comprehensive server environment info (similar to Zscaler)
$environmentVars = [];

// Add key server variables that Zscaler shows
$serverVarsToShow = [
    'HTTP_ACCEPT',
    'HTTP_ACCEPT_ENCODING',
    'HTTP_ACCEPT_LANGUAGE',
    'HTTP_CACHE_CONTROL',
    'HTTP_CONNECTION',
    'HTTP_DNT',
    'HTTP_HOST',
    'HTTP_PRAGMA',
    'HTTP_UPGRADE_INSECURE_REQUESTS',
    'HTTP_USER_AGENT',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED_PROTO',
    'HTTP_X_REAL_IP',
    'REQUEST_METHOD',
    'REQUEST_URI',
    'REQUEST_SCHEME',
    'SERVER_PROTOCOL',
    'REMOTE_ADDR',
    'REMOTE_PORT',
    'SERVER_NAME',
    'SERVER_PORT',
    'HTTPS',
    'CONTENT_TYPE',
    'CONTENT_LENGTH',
    'QUERY_STRING',
    'REQUEST_TIME_FLOAT',
    'REQUEST_TIME'
];

foreach ($_SERVER as $key => $value) {
    // Include the specified server vars
    if (in_array($key, $serverVarsToShow)) {
        $environmentVars[$key] = $value;
    }
    // Include all HTTP_ headers
    elseif (strpos($key, 'HTTP_') === 0) {
        $environmentVars[$key] = $value;
    }
}

// Additional computed values
$environmentVars['GATEWAY_INTERFACE'] = 'CGI/1.1';
$environmentVars['FCGI_ROLE'] = 'RESPONDER';
$environmentVars['REDIRECT_STATUS'] = '200';

// Sort environment variables alphabetically for consistent display
ksort($environmentVars);

// Create comprehensive server info
$serverInfo = [
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '/',
    'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1',
    'REMOTE_ADDR' => $clientIP,
    'REMOTE_PORT' => $_SERVER['REMOTE_PORT'] ?? '',
    'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost',
    'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? ($_SERVER['HTTPS'] ? '443' : '80'),
    'HTTPS' => isset($_SERVER['HTTPS']) ? 'Yes' : 'No',
    'REQUEST_TIME' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
    'GATEWAY_IP' => $gatewayIP,
    'CLIENT_IP_AS_SEEN_BY_SERVER' => $clientIP
];

// Main response data
$response = [
    'headers' => $headers,
    'server_info' => $serverInfo,
    'environment_variables' => $environmentVars,
    'proxy_detection' => [
        'is_proxy' => $proxyInfo['isProxy'],
        'is_zscaler' => $proxyInfo['isZscaler'],
        'is_deere_zscaler' => $proxyInfo['isDeereZscaler'],
        'detected_headers' => $proxyInfo['detectedHeaders'],
        'proxy_chain' => $proxyInfo['proxyChain']
    ],
    'forwarding_info' => $forwardingInfo,
    'ip_addresses' => [
        'client_ip' => $clientIP,
        'gateway_ip' => $gatewayIP,
        'x_forwarded_for' => $forwardingInfo['X-Forwarded-For'] ?? null
    ],
    'timestamp' => date('c'),
    
    // Legacy compatibility
    'zscaler_detected' => $proxyInfo['isZscaler'],
    'forwarded_info' => [
        'x_forwarded_for' => $forwardingInfo['X-Forwarded-For'] ?? 'Not present',
        'x_real_ip' => $forwardingInfo['X-Real-IP'] ?? 'Not present'
    ]
];

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>
