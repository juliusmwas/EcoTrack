<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../../config.php";

// Handle new bin addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['location'], $_POST['latitude'], $_POST['longitude'])) {
    $location = trim($_POST['location']);
    $lat = trim($_POST['latitude']);
    $lng = trim($_POST['longitude']);

    // ✅ Insert with default status
    $stmt = $pdo->prepare("INSERT INTO bins (location, latitude, longitude, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$location, $lat, $lng]);
}

// ✅ Fetch bins (use ID instead of created_at to avoid missing column)
$query = "SELECT * FROM bins ORDER BY id DESC";
$stmt = $pdo->query($query);
$bins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bins | EcoTrack Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
            --light-bg: #F5F8F7;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: var(--light-bg);
            margin: 0;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .form-section {
            background: #fff;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 8px var(--shadow);
            margin-bottom: 2rem;
        }

        input {
            width: calc(100% - 1rem);
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 0.8rem;
        }

        button {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: var(--accent);
        }

        #map {
            height: 400px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 3px 8px var(--shadow);
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: #fff;
        }

        tr:hover {
            background: #f2f9f8;
        }

        @media(max-width:768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-map-pin-line"></i> Manage Waste Bins</h2>

            <div class="form-section">
                <form method="POST">
                    <input type="text" name="location" placeholder="Enter location name..." required>
                    <input type="text" name="latitude" placeholder="Latitude" required>
                    <input type="text" name="longitude" placeholder="Longitude" required>
                    <button type="submit">Add Bin</button>
                </form>
            </div>

            <div id="map"></div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bins): ?>
                        <?php foreach ($bins as $b): ?>
                            <tr>
                                <td><?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['location']) ?></td>
                                <td><?= htmlspecialchars($b['latitude']) ?></td>
                                <td><?= htmlspecialchars($b['longitude']) ?></td>
                                <td><?= htmlspecialchars($b['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No bins found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const map = L.map('map').setView([-1.286389, 36.817223], 12); // Nairobi default

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const bins = <?= json_encode($bins) ?>;

        bins.forEach(b => {
            if (b.latitude && b.longitude) {
                const color = b.status === 'pending' ? 'gray' :
                    b.status === 'in-progress' ? 'orange' :
                    b.status === 'resolved' ? 'green' :
                    'blue';

                const marker = L.circleMarker([b.latitude, b.longitude], {
                    color,
                    radius: 8,
                    fillOpacity: 0.8
                }).addTo(map);

                marker.bindPopup(`<b>${b.location}</b><br>Status: ${b.status}`);
            }
        });
    </script>
</body>

</html>