<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once "../config.php"; // Make sure DB connection is correct

// Fetch all waste reports from DB
$query = "SELECT id, location, status, created_at FROM waste_reports ORDER BY created_at DESC";
$result = $conn->query($query);

$bins = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Expecting "lat, lng" format in location
        $coords = explode(",", $row['location']);
        if (count($coords) == 2) {
            $bins[] = [
                'id' => $row['id'],
                'lat' => trim($coords[0]),
                'lng' => trim($coords[1]),
                'status' => $row['status'],
                'created_at' => $row['created_at']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View | EcoTrack Collector</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.15);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            min-height: 100vh;
            background: #f9f9f9;
        }

        .map-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .map-container {
            flex: 1;
            width: 100%;
            height: calc(100vh - 70px);
        }

        .map-title {
            background: var(--primary);
            color: #fff;
            padding: 1rem;
            font-size: 1.2rem;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 2px 6px var(--shadow);
        }

        /* ✅ Responsive Adjustments */
        @media (max-width: 768px) {
            .map-title {
                font-size: 1rem;
                padding: 0.8rem;
            }

            .map-container {
                height: calc(100vh - 60px);
            }
        }

        @media (max-width: 480px) {
            .map-title {
                font-size: 0.95rem;
                padding: 0.7rem;
            }

            .map-container {
                height: calc(100vh - 55px);
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="map-wrapper">
        <?php include 'navbar.php'; ?>
        <div class="map-title">
            <i class="ri-map-pin-line"></i> Waste Bin Locations
        </div>
        <div id="map" class="map-container"></div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const map = L.map('map').setView([1.2921, 36.8219], 13); // Default: Nairobi

        // Load OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Bin data from PHP
        const bins = <?php echo json_encode($bins); ?>;

        // Marker color based on status
        function getColor(status) {
            switch (status) {
                case 'pending':
                    return 'orange';
                case 'in-progress':
                    return 'blue';
                case 'resolved':
                    return 'green';
                default:
                    return 'gray';
            }
        }

        bins.forEach(bin => {
            const marker = L.circleMarker([bin.lat, bin.lng], {
                radius: 10,
                fillColor: getColor(bin.status),
                color: '#333',
                weight: 1,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(`
                <b>Report ID:</b> ${bin.id}<br>
                <b>Status:</b> ${bin.status}<br>
                <b>Location:</b> ${bin.lat}, ${bin.lng}<br>
                <b>Reported On:</b> ${bin.created_at}
            `);
        });

        // Auto center if bins exist
        if (bins.length > 0) {
            const bounds = L.latLngBounds(bins.map(b => [b.lat, b.lng]));
            map.fitBounds(bounds, {
                padding: [50, 50]
            });
        }
    </script>
</body>

</html>