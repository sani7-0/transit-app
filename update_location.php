<?php
// update_location.php

// Enable errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow session cookie over HTTPS
ini_set('session.cookie_samesite','None');
ini_set('session.cookie_secure','1');
session_start();

// Debug: dump session to file
file_put_contents(__DIR__ . '/debug_session.txt', print_r($_SESSION, true));

//
// 1) Read incoming data (JSON or form‑urlencoded)
//
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    // fallback to $_POST for form‑urlencoded
    $data = $_POST;
}

// Extract values
$driver_id = isset($_SESSION['driver_id']) ? intval($_SESSION['driver_id']) : null;
$latitude  = isset($data['latitude'])  ? floatval($data['latitude'])  : null;
$longitude = isset($data['longitude']) ? floatval($data['longitude']) : null;

// Debug: dump what we received
file_put_contents(__DIR__ . '/debug_update.txt', json_encode([
    'session_driver_id' => $driver_id,
    'payload'           => $data
], JSON_PRETTY_PRINT));

// Validate
if (!$driver_id) {
    die(json_encode([ 'error' => '❌ Not logged in (no driver_id in session)' ]));
}
if ($latitude === null || $longitude === null) {
    die(json_encode([ 'error' => '❌ Missing latitude or longitude' ]));
}

//
// 2) Connect to your remote MySQL
//
$servername = "sql7.freesqldatabase.com";
$username   = "sql7777349";
$password   = "8Iib6bgQeK";
$database   = "sql7777349";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode([ 'error' => "❌ DB connection failed: " . $conn->connect_error ]));
}

//
// 3) Update the driver_locations table
//
$sql = "INSERT INTO bus_driver_locations (driver_id, latitude, longitude, last_updated)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE latitude = VALUES(latitude),
                                longitude = VALUES(longitude),
                                last_updated = NOW()";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode([ 'error' => "❌ Prepare failed: " . $conn->error ]));
}

$stmt->bind_param("idd", $driver_id, $latitude, $longitude);

if ($stmt->execute()) {
    echo json_encode([ 'success' => '✅ Location updated', 'driver_id' => $driver_id ]);
} else {
    echo json_encode([ 'error' => "❌ Execute failed: " . $stmt->error ]);
}

$stmt->close();
$conn->close();
