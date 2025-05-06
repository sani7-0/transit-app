<?php
// get_stops.php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "transit");
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed"]));
}

$sql = "SELECT * FROM stops ORDER BY route_id, stop_order";
$result = $conn->query($sql);

$stopsByRoute = [];

while ($row = $result->fetch_assoc()) {
    $routeId = $row['route_id'];
    if (!isset($stopsByRoute[$routeId])) {
        $stopsByRoute[$routeId] = [];
    }
    $stopsByRoute[$routeId][] = [
        'id' => $row['id'],
        'name' => $row['stop_name'],
        'lat' => $row['lat'],
        'lng' => $row['lng']
    ];
}

$conn->close();
echo json_encode($stopsByRoute);
?>
