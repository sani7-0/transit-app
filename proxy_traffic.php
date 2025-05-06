<?php
// proxy_traffic.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$TOMTOM_API_KEY = "jiQMU8KzhfsLK092SOvwJI0KEpGLJUUG";

// Use bbox from query param or fallback to default
$bbox = isset($_GET['bbox']) ? $_GET['bbox'] : "39,8,42,10";

// Build TomTom Traffic API URL
$url = "https://api.tomtom.com/traffic/services/5/incidents?bbox=$bbox&key=$TOMTOM_API_KEY&contentType=json";

// Use cURL to fetch from TomTom
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // for localhost only

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(["error" => "cURL error: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

if ($http_code !== 200) {
    echo json_encode(["error" => "TomTom API returned HTTP $http_code"]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Send TomTom's response directly to frontend
echo $response;
