<?php
header('Content-Type: application/json');
$TOMTOM_API_KEY = "TJ1OHQZFL3Gsaeg1GE0SdmB5347JETtr";

$conn = new mysqli("localhost", "root", "", "transit");
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed"]));
}

// Get latest client location
$clientRes = $conn->query("SELECT latitude, longitude FROM client_locations ORDER BY last_updated DESC LIMIT 1");
if ($clientRes->num_rows == 0) {
    die(json_encode(["error" => "No client location found"]));
}
$client = $clientRes->fetch_assoc();
$clientLat = $client['latitude'];
$clientLng = $client['longitude'];

// Get all driver locations
$driverRes = $conn->query("SELECT driver_id, latitude, longitude FROM bus_driver_locations");
$nearest = null;
$minDist = PHP_INT_MAX;

while ($row = $driverRes->fetch_assoc()) {
    $dLat = $row['latitude'];
    $dLng = $row['longitude'];
    $dist = sqrt(pow($clientLat - $dLat, 2) + pow($clientLng - $dLng, 2));

    if ($dist < $minDist) {
        $minDist = $dist;
        $nearest = $row;
    }
}

if (!$nearest) {
    die(json_encode(["error" => "No drivers available"]));
}

// Call TomTom API for ETA
$start = "{$nearest['latitude']},{$nearest['longitude']}";
$end = "{$clientLat},{$clientLng}";
$url = "https://api.tomtom.com/routing/1/calculateRoute/$start:$end/json?key=$TOMTOM_API_KEY";

$response = file_get_contents($url);
$data = json_decode($response, true);

if (isset($data['routes'][0]['summary']['travelTimeInSeconds'])) {
    $etaSeconds = $data['routes'][0]['summary']['travelTimeInSeconds'];
    $etaMinutes = round($etaSeconds / 60);
    echo json_encode([
        "eta_minutes" => $etaMinutes,
        "from_driver" => $nearest['driver_id']
    ]);
} else {
    echo json_encode(["error" => "Failed to get ETA"]);
}
?>
