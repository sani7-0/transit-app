<?php
session_start();
file_put_contents("debug_session.txt", print_r($_SESSION, true));


error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "transit";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "❌ Connection failed: " . $conn->connect_error]));
}

// Validate input
if (!isset($_POST['latitude']) || !isset($_POST['longitude'])) {
    die(json_encode(["error" => "❌ Missing required parameters."]));
}

if (!isset($_SESSION['driver_id']) || !isset($_SESSION['driver_route'])) {
    die(json_encode(["error" => "❌ Not logged in or missing route information."]));
}

$driver_id = intval($_SESSION['driver_id']);
$route = $_SESSION['driver_route']; // Get route from session
$latitude = filter_var($_POST['latitude'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($_POST['longitude'], FILTER_VALIDATE_FLOAT);

if ($latitude === false || $longitude === false) {
    die(json_encode(["error" => "❌ Invalid latitude or longitude."]));
}

// Check driver exists
$checkDriver = $conn->prepare("SELECT id FROM bus_drivers WHERE id = ?");
$checkDriver->bind_param("i", $driver_id);
$checkDriver->execute();
$checkDriver->store_result();

if ($checkDriver->num_rows === 0) {
    die(json_encode(["error" => "❌ Driver ID does not exist."]));
}

// Corrected SQL query
$sql = "INSERT INTO bus_driver_locations (driver_id, latitude, longitude, route, last_updated) 
        VALUES (?, ?, ?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE 
          latitude = VALUES(latitude), 
          longitude = VALUES(longitude), 
          route = VALUES(route), 
          last_updated = NOW()";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "❌ SQL error: " . $conn->error]));
}

// Bind parameters (i = integer, d = double, s = string)
$stmt->bind_param("idds", $driver_id, $latitude, $longitude, $route);

if ($stmt->execute()) {
    echo json_encode(["success" => "✅ Location updated successfully", "driver_id" => $driver_id]);
} else {
    echo json_encode(["error" => "❌ Error updating location: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>