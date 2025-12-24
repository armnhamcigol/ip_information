<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Get all HTTP headers
\ = array();

// Method 1: Using getallheaders() if available
if (function_exists('getallheaders')) {
    \ = getallheaders();
} else {
    // Method 2: Parse \ array for headers
    foreach (\ as \ => \) {
        if (strpos(\, 'HTTP_') === 0) {
            \ = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr(\, 5)))));
            \[\] = \;
        }
    }
    
    // Add some common headers that might not have HTTP_ prefix
    if (isset(\['CONTENT_TYPE'])) {
        \['Content-Type'] = \['CONTENT_TYPE'];
    }
    if (isset(\['CONTENT_LENGTH'])) {
        \['Content-Length'] = \['CONTENT_LENGTH'];
    }
}

// Check for Deere Zscaler headers
\ = false;
\ = array();

foreach (\ as \ => \) {
    if (stripos(\, 'deere-zscaler') !== false || stripos(\, 'deere-zscaler') !== false) {
        \ = true;
        \[\] = \;
    }
}

// Extract X-Forwarded-For information
// Check multiple possible header variations
\ = null;
\ = null;
\ = null;
\ = null;

// Check headers array first (case-sensitive)
foreach (\ as \ => \) {
    \ = strtolower(\);
    if (\ === 'x-forwarded-for' && !\) \ = \;
    if (\ === 'x-real-ip' && !\) \ = \;
    if (\ === 'x-forwarded-proto' && !\) \ = \;
    if (\ === 'x-forwarded-host' && !\) \ = \;
}

\ = array(
    'x_forwarded_for' => \ ?? \['HTTP_X_FORWARDED_FOR'] ?? 'Not present',
    'x_real_ip' => \ ?? \['HTTP_X_REAL_IP'] ?? 'Not present',
    'x_forwarded_proto' => \ ?? \['HTTP_X_FORWARDED_PROTO'] ?? 'Not present',
    'x_forwarded_host' => \ ?? \['HTTP_X_FORWARDED_HOST'] ?? 'Not present',
    'original_ip' => \['REMOTE_ADDR'] ?? 'Unknown',
    'all_server_vars' => array_filter(\, function(\) {
        return strpos(\, 'HTTP_X_') === 0;
    }, ARRAY_FILTER_USE_KEY)
);

// Get current timestamp from multiple sources for debugging
// Since the container might have wrong system time, calculate from known reference
\ = time();
\ = \['REQUEST_TIME'] ?? 0;

// If system time is clearly wrong (before 2020), use a workaround
\ = \;
if (\ < 1577836800) { // Jan 1, 2020 timestamp
    // System time is wrong, use a calculated offset
    // Current date should be around July 11, 2025
    \ = 1752326400; // Approximate timestamp for July 11, 2025
}

// Determine the actual client IP as seen by the server
// This should be the Zscaler public IP when coming through Zscaler
\ = \['REMOTE_ADDR'] ?? 'Unknown';

// Add additional server information
\ = array(
    'REQUEST_METHOD' => \['REQUEST_METHOD'] ?? 'Unknown',
    'REQUEST_URI' => \['REQUEST_URI'] ?? 'Unknown',
    'SERVER_PROTOCOL' => \['SERVER_PROTOCOL'] ?? 'Unknown',
    'REMOTE_ADDR' => \['REMOTE_ADDR'] ?? 'Unknown',
    'REMOTE_PORT' => \['REMOTE_PORT'] ?? 'Unknown',
    'SERVER_NAME' => \['SERVER_NAME'] ?? 'Unknown',
    'SERVER_PORT' => \['SERVER_PORT'] ?? 'Unknown',
    'HTTPS' => isset(\['HTTPS']) ? 'Yes' : 'No',
    'REQUEST_TIME' => gmdate('Y-m-d H:i:s', \) . ' UTC',
    'REQUEST_TIME_RAW' => \,
    'CURRENT_TIME_RAW' => \,
    'CORRECTED_TIME_RAW' => \,
    'CLIENT_IP_AS_SEEN_BY_SERVER' => \
);

// Return the data as JSON with Zscaler detection
echo json_encode(array(
    'headers' => \,
    'server_info' => \,
    'zscaler_detected' => \,
    'zscaler_headers' => \,
    'forwarded_info' => \,
    'timestamp' => gmdate('Y-m-d H:i:s', \) . ' UTC'
), JSON_PRETTY_PRINT);
?>
