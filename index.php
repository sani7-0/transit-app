<?php 
session_start();
if (!isset($_SESSION['driver_id'])) {
    header("Location: login.php");
    exit();
}
$driver_id = $_SESSION['driver_id']; // Get driver ID from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Location Tracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body, html { 
            background: cornflowerblue;
            font-family: sans-serif;
        }
        .header {
            padding: 1.5rem;
            text-align: center;
        }
        .header h1 {
            color: #fff;
            font-size: 1.8rem;
            margin: 0;
            font-weight: bold;
        }
        .coordinates {
            color: #fff;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        #map { 
            height: 70vh; 
            width: 90%; 
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 5px 10px #ddd;
        }
        .logout-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 15px;
            background: red;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>Location Tracker</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div class="coordinates" id="coordinates">Acquiring location...</div>
    </header>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        let map, marker, lastLat = null, lastLng = null;
        const driverId = <?php echo $driver_id; ?>; // Get driver ID dynamically

        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(updateLocation, handleError, { enableHighAccuracy: true });
        } else {
            alert("❌ Geolocation is not supported by this browser.");
        }

        function updateLocation(position) {
            let lat = position.coords.latitude;
            let lng = position.coords.longitude;
            document.getElementById('coordinates').textContent = `${lat.toFixed(6)}°, ${lng.toFixed(6)}°`;

            if (!map) {
                map = L.map('map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                marker = L.marker([lat, lng]).addTo(map).bindPopup("Your Current Location").openPopup();
            } else {
                marker.setLatLng([lat, lng]);
            }

            if (lastLat !== null && lastLng !== null) {
                let distance = getDistanceFromLatLonInMeters(lastLat, lastLng, lat, lng);
                if (distance < 5) return; // Ignore small movements
            }

            lastLat = lat;
            lastLng = lng;

            sendLocationToServer(driverId, lat, lng);
        }

        function handleError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("❌ Location access denied.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("⚠️ Location unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("⌛ Location request timed out.");
                    break;
                default:
                    alert("❗ Unknown location error.");
            }
        }

        function sendLocationToServer(driverId, lat, lng) {
            fetch('update_location.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    driver_id: driverId,
                    latitude: lat,
                    longitude: lng
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("❌ Server Error:", data.error);
                    alert(data.error);
                } else {
                    console.log("✅ Success:", data.success);
                }
            })
            .catch(error => {
                console.error('❌ Fetch Error:', error);
                alert("❌ Failed to send location to server.");
            });
        }

        function fetchDriverLocation() {
            fetch(`get_locations.php?driver_id=${driverId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        let lat = parseFloat(data.latitude);
                        let lng = parseFloat(data.longitude);
                        if (marker) {
                            marker.setLatLng([lat, lng]);
                        } else {
                            marker = L.marker([lat, lng]).addTo(map).bindPopup("Driver Location").openPopup();
                        }
                        map.setView([lat, lng], 15);
                    } else if (data.error) {
                        console.error(data.error);
                    }
                })
                .catch(error => console.error('Error fetching driver location:', error));
        }

        setInterval(fetchDriverLocation, 5000);

        function getDistanceFromLatLonInMeters(lat1, lon1, lat2, lon2) {
            const R = 6371e3;
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c;
        }
    </script>
</body>
</html>
