<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once "../config.php";

// Fetch bins (waste reports) from the database
$query = "SELECT id, location, status, created_at FROM waste_reports";
$stmt = $pdo->query($query);
$bins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map View | EcoTrack Collector</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
            --light-bg: #F5F8F7;
        }

        .map-wrapper {
            margin: 2rem auto;
            max-width: 1100px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow);
            padding: 1.5rem;
        }

        #map {
            width: 100%;
            height: 500px;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .legend {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
        }

        .legend span {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            border-radius: 3px;
        }

        @media (max-width: 768px) {
            .map-wrapper {
                padding: 1rem;
                margin: 1rem;
            }

            #map {
                height: 380px;
            }

            h2 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <div class="map-wrapper">
                <h2><i class="ri-map-pin-line"></i> Waste Bin Map Overview</h2>
                <p>View all reported bins and their current statuses in real time.</p>

                <div id="map"></div>
            </div>
        </div>
    </div>

    <script>
        const bins = <?php echo json_encode($bins); ?>;

        // Initialize map
        const map = L.map('map').setView([-0.3320, 37.6448], 14);

        // Tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const colors = {
            "empty": "green",
            "half-full": "blue",
            "full": "orange",
            "overflowing": "red",
            "pending": "gray",
            "in-progress": "#17a2b8",
            "resolved": "#28a745"
        };

        bins.forEach(bin => {
            if (!bin.location) return;
            const coords = bin.location.split(',');
            if (coords.length !== 2) return;

            const lat = parseFloat(coords[0]);
            const lng = parseFloat(coords[1]);

            if (isNaN(lat) || isNaN(lng)) return;

            const status = bin.status || 'pending';
            const color = colors[status] || 'gray';

            const marker = L.circleMarker([lat, lng], {
                color: color,
                radius: 9,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(`
                <strong>Bin ID: #${bin.id}</strong><br>
                Status: <span style="color:${color}; font-weight:bold;">${status}</span><br>
                Last Updated: ${bin.created_at}
            `);
        });

        // Add Legend
        const legend = L.control({
            position: "bottomright"
        });
        legend.onAdd = function() {
            const div = L.DomUtil.create("div", "legend");
            div.innerHTML += "<h4>Status Legend</h4>";
            div.innerHTML += '<span style="background:green"></span>Empty<br>';
            div.innerHTML += '<span style="background:blue"></span>Half Full<br>';
            div.innerHTML += '<span style="background:orange"></span>Full<br>';
            div.innerHTML += '<span style="background:red"></span>Overflowing<br>';
            div.innerHTML += '<span style="background:gray"></span>Pending';
            return div;
        };
        legend.addTo(map);
    </script>
</body>

</html>