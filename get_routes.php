<?php
// get_routes.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "transit";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "DB connection failed: " . $conn->connect_error]));
}

$TOMTOM_API_KEY = "TJ1OHQZFL3Gsaeg1GE0SdmB5347JETtr";

$sql = "SELECT * FROM routes";
$result = $conn->query($sql);
if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$routes = [];

while ($route = $result->fetch_assoc()) {
    $routeId = $route['id'];

    // Fetch stops
    $stops = [];
    $stopQuery = "SELECT name, latitude AS lat, longitude AS lng FROM stops WHERE route_id = $routeId ORDER BY stop_order ASC";
    $stopResult = $conn->query($stopQuery);
    if ($stopResult && $stopResult->num_rows > 0) {
        while ($stop = $stopResult->fetch_assoc()) {
            $stops[] = $stop;
        }
    }

    // Prepare waypoints
    $waypoints = [];

    // Add starting point
    if (!empty($route['start_lat']) && !empty($route['start_lng'])) {
        $waypoints[] = "{$route['start_lat']},{$route['start_lng']}";
    }

    // Add all stops
    foreach ($stops as $stop) {
        $waypoints[] = "{$stop['lat']},{$stop['lng']}";
    }

    // Add ending point
    if (!empty($route['end_lat']) && !empty($route['end_lng'])) {
        $waypoints[] = "{$route['end_lat']},{$route['end_lng']}";
    }

    $coordinates = [];

    // Snap to road using TomTom
    if (count($waypoints) > 1) {
        $coordinateStr = implode(":", $waypoints);
        $tomtom_url = "https://api.tomtom.com/routing/1/calculateRoute/$coordinateStr/json?key=$TOMTOM_API_KEY";

        $response = @file_get_contents($tomtom_url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['routes'][0]['legs'])) {
                foreach ($data['routes'][0]['legs'] as $leg) {
                    if (isset($leg['points'])) {
                        foreach ($leg['points'] as $point) {
                            $coordinates[] = [
                                'lat' => $point['latitude'],
                                'lng' => $point['longitude']
                            ];
                        }
                    }
                }
            } else {
                error_log("❌ No route legs found for route $routeId");
            }
        } else {
            error_log("❌ Failed to fetch route $routeId from TomTom API");
        }
    }

    $routes[] = [
        'id' => $routeId,
        'route_name' => $route['route_name'],
        'route_color' => $route['route_color'] ?? '#007bff',
        'coordinates' => $coordinates,
        'stops' => $stops
    ];
}

$conn->close();
echo json_encode($routes);
?>
