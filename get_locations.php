<?php
// get_locations.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "transit";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "❌ Connection failed: " . $conn->connect_error]));
}

$twoMinutesAgo = date('Y-m-d H:i:s', strtotime('-2 minutes'));

$sql = "SELECT l.driver_id, l.latitude, l.longitude, d.route, r.route_name, r.route_color
        FROM bus_driver_locations l
        JOIN bus_drivers d ON l.driver_id = d.id
        JOIN routes r ON d.route = r.id
        INNER JOIN (
            SELECT driver_id, MAX(last_updated) AS latest
            FROM bus_driver_locations
            GROUP BY driver_id
            HAVING latest >= '$twoMinutesAgo' 
        ) AS latest_locations 
        ON l.driver_id = latest_locations.driver_id 
        AND l.last_updated = latest_locations.latest";

$result = $conn->query($sql);
if (!$result) {
    die(json_encode(["error" => "❌ Query failed: " . $conn->error]));
}

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = [
        'user_id' => $row['driver_id'],
        'latitude' => (float)$row['latitude'],
        'longitude' => (float)$row['longitude'],
        'route' => $row['route'],
        'route_name' => $row['route_name'],
        'route_color' => $row['route_color']
    ];
}

echo json_encode($locations);
$conn->close();
?>
