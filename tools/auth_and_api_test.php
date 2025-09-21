<?php
/**
 * Authentication and Protected API Test
 * Logs in with admin credentials, captures session cookies, and calls protected endpoints
 */

$baseUrl = 'http://localhost:8080';
$loginUrl = $baseUrl . '/auth/login';
$credentials = [
    'email' => 'admin@perfectsmile.com',
    'password' => 'password'
];

function http_post($url, $data, $cookies = []) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: AuthTest/1.0'
            ],
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];

    if (!empty($cookies)) {
        $cookieHeader = [];
        foreach ($cookies as $k => $v) {
            $cookieHeader[] = $k . '=' . $v;
        }
        $options['http']['header'][] = 'Cookie: ' . implode('; ', $cookieHeader);
    }

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];

    return [$result, $responseHeaders];
}

function extractCookies($headers) {
    $cookies = [];
    foreach ($headers as $h) {
        if (stripos($h, 'Set-Cookie:') === 0) {
            $parts = explode(':', $h, 2);
            if (isset($parts[1])) {
                $cookieParts = explode(';', trim($parts[1]));
                $kv = explode('=', trim($cookieParts[0]), 2);
                if (isset($kv[1])) {
                    $cookies[$kv[0]] = $kv[1];
                }
            }
        }
    }
    return $cookies;
}

// 1) Perform login
echo "=== Authentication Test ===\n";
$loginEndpoint = $baseUrl . '/auth/login';

[$loginRes, $loginHeaders] = http_post($loginEndpoint, $credentials);
$cookies = extractCookies($loginHeaders);

// Debug: print login response headers and snippet to inspect Set-Cookie attributes
echo "\n--- Login Response Headers ---\n" . implode("\n", $loginHeaders) . "\n";
echo "--- Login Response Body (first 1000 chars) ---\n" . substr($loginRes, 0, 1000) . "\n\n";

if (empty($cookies)) {
    echo "❌ Login did not return cookies. Response length: " . strlen($loginRes) . "\n";
    echo "Headers:\n" . implode("\n", $loginHeaders) . "\n";
    exit(1);
}

echo "✅ Login successful - cookies received:\n";
foreach ($cookies as $k => $v) echo "   - {$k}={$v}\n";

// 2) Fetch admin appointments page to extract CSRF token
$adminPageUrl = $baseUrl . '/admin/appointments';
// Use GET with cookies
function http_get($url, $cookies = []) {
    $options = [
        'http' => [
            'method'  => 'GET',
            'header'  => ['User-Agent: AuthTest/1.0'],
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];
    if (!empty($cookies)) {
        $cookieHeader = [];
        foreach ($cookies as $k => $v) {
            $cookieHeader[] = $k . '=' . $v;
        }
        $options['http']['header'][] = 'Cookie: ' . implode('; ', $cookieHeader);
    }
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    return [$result, $responseHeaders];
}

echo "\nFetching admin page to extract CSRF token...\n";
[ $adminHtml, $adminHeaders ] = http_get($adminPageUrl, $cookies);

// Try to extract CSRF token. Prefer meta tag, fallback to hidden input.
$csrf = null;
// 1) Try DOM parsing if available
if (function_exists('libxml_use_internal_errors')) {
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    if (@$doc->loadHTML($adminHtml)) {
        $metas = $doc->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            $name = $meta->getAttribute('name');
            if (strtolower($name) === 'csrf-token') {
                $csrf = $meta->getAttribute('content');
                break;
            }
        }
        if (!$csrf) {
            // Look for input fields
            $inputs = $doc->getElementsByTagName('input');
            foreach ($inputs as $input) {
                $iname = $input->getAttribute('name');
                if (stripos($iname, 'csrf') !== false) {
                    $csrf = $input->getAttribute('value');
                    break;
                }
            }
        }
    }
    libxml_clear_errors();
}

// 2) Fallback to simple string parsing if still not found
if (!$csrf) {
    // Look for meta tag name="csrf-token" or name='csrf-token'
    $pos = stripos($adminHtml, 'name="csrf-token"');
    if ($pos === false) $pos = stripos($adminHtml, "name='csrf-token'");
    if ($pos !== false) {
        // find content attribute after that position
        $contentPos = stripos($adminHtml, 'content=', $pos);
        if ($contentPos !== false) {
            $start = $contentPos + strlen('content=');
            $quote = $adminHtml[$start] ?? '"';
            if ($quote === '"' || $quote === "'") {
                $start++;
                $end = strpos($adminHtml, $quote, $start);
                if ($end !== false) {
                    $csrf = substr($adminHtml, $start, $end - $start);
                }
            }
        }
    }

    // If still not found, search for input fields with name containing 'csrf'
    if (!$csrf) {
        if (preg_match_all('/<input[^>]+>/i', $adminHtml, $inputMatches)) {
            foreach ($inputMatches[0] as $inputTag) {
                if (stripos($inputTag, 'name=') !== false && stripos($inputTag, 'csrf') !== false) {
                    if (preg_match('/value=["\']([^"\']+)["\']/i', $inputTag, $v)) {
                        $csrf = $v[1];
                        break;
                    }
                }
            }
        }
    }
}

if ($csrf) {
    echo "✅ Extracted CSRF token: " . substr($csrf, 0, 40) . "...\n";
} else {
    echo "⚠️  CSRF token not found on admin page. Admin page content length: " . strlen($adminHtml) . "\n";
}

// Helper to make authenticated POST with CSRF header
function http_post_auth($url, $data, $cookies = [], $csrfToken = null) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => [
                'Content-Type: application/x-www-form-urlencoded',
                'User-Agent: AuthTest/1.0'
            ],
            'content' => http_build_query($data),
            'ignore_errors' => true,
            'timeout' => 10
        ]
    ];
    if (!empty($cookies)) {
        $cookieHeader = [];
        foreach ($cookies as $k => $v) {
            $cookieHeader[] = $k . '=' . $v;
        }
        $options['http']['header'][] = 'Cookie: ' . implode('; ', $cookieHeader);
    }
    if ($csrfToken) {
        $options['http']['header'][] = 'X-CSRF-TOKEN: ' . $csrfToken;
    }
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $responseHeaders = $http_response_header ?? [];
    return [$result, $responseHeaders];
}

// 3) Call protected endpoint /appointments/available-slots (POST) with CSRF header
$slotsUrl = $baseUrl . '/appointments/available-slots';
$slotsPayload = [
    'date' => date('Y-m-d', strtotime('+7 days')),
    'branch_id' => 1,
    'service_id' => 2,
    'granularity' => 15
];

[$slotsRes, $slotsHeaders] = http_post_auth($slotsUrl, $slotsPayload, $cookies, $csrf);

echo "\n=== /appointments/available-slots Response ===\n";
echo "HTTP Headers:\n" . implode("\n", $slotsHeaders) . "\n";
echo "Response Body (first 1000 chars):\n" . substr($slotsRes, 0, 1000) . "\n";

// 4) Create a protected appointment via /admin/appointments/create endpoint (admin group route)
$createUrl = $baseUrl . '/admin/appointments/create';
$createPayload = [
    'user_id' => 3, // existing patient
    'branch_id' => 1,
    'dentist_id' => 30,
    'appointment_datetime' => date('Y-m-d H:i:s', strtotime('+8 days 09:00:00')),
    'procedure_duration' => 60,
    'approval_status' => 'approved',
    'status' => 'confirmed'
];

// Add CSRF form field fallback name '_token'
if ($csrf) {
    $createPayload['_token'] = $csrf;
}

[$createRes, $createHeaders] = http_post_auth($createUrl, $createPayload, $cookies, $csrf);

echo "\n=== /admin/appointments/create Response ===\n";
echo "HTTP Headers:\n" . implode("\n", $createHeaders) . "\n";
echo "Response Body (first 1000 chars):\n" . substr($createRes, 0, 1000) . "\n";

echo "\nAuthentication and protected endpoint tests completed.\n";