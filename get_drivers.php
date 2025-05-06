<?php
$servername = "localhost";  // Change if using a different server
$username = "root";         // Change if you set a MySQL password
$password = "";             // Default for XAMPP is empty
$dbname = "transit";  // Make sure this is correct

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Debugging: Show error if query fails
$sql = "SELECT driver_id, latitude, longitude FROM bus_driver_locations";
$result = $conn->query($sql);

if (!$result) {
    die("SQL Query Failed: " . $conn->error);
}

$drivers = [];
while ($row = $result->fetch_assoc()) {
    $drivers[] = $row;
}

// Send JSON response
echo json_encode($drivers);

$conn->close();
?>