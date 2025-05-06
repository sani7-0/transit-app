<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "sql7.freesqldatabase.com";
$username = "sql7777349";
$password = "8Iib6bgQeK";  // Replace this once it finishes loading
$database = "sql7777349";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);
$lat = $data['latitude'] ?? null;
$lng = $data['longitude'] ?? null;

if (!$lat || !$lng) {
    die(json_encode(["error" => "Invalid coordinates"]));
}

$sql = "INSERT INTO client_locations (latitude, longitude) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dd", $lat, $lng);
$stmt->execute();

echo json_encode(["success" => true]);
?>
