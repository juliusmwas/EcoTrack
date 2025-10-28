<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
    header("Location: ../login.php");
    exit;
}
require_once '../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard | EcoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --light-bg: #F5F8F7;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        /* --- Main Layout Adjustment --- */
        .main-content {
            margin-left: 230px;
            /* same width as your sidebar */
            padding: 2rem;
            background: var(--light-bg);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Adjust for mobile view */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .report-card h3 {
                color: var(--primary);
                font-size: 1rem;
                margin-bottom: 1rem;
            }

        }


        .map-container {
            width: 100%;
            height: 420px;
            border-radius: 12px;
            overflow: hidden;
            margin: 1rem 0 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* --- Waste Report Form --- */
        .report-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow);
            padding: 2rem;
            margin-top: 2rem;
            max-width: 700px;
        }

        .report-card h3 {
            color: var(--primary);
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .report-form label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 600;
            color: #333;
        }

        .report-form input,
        .report-form select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: 0.3s;
        }

        .report-form input:focus,
        .report-form select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 5px rgba(66, 184, 131, 0.3);
        }

        .report-form button {
            background: var(--accent);
            color: #fff;
            padding: 0.9rem 1.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .report-form button:hover {
            background: #37a471;
            transform: translateY(-2px);
        }

        /* --- Table Styling --- */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px var(--shadow);
            overflow: hidden;
        }

        thead tr {
            background: var(--primary);
            color: #fff;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        tbody tr:hover {
            background: #f0f7f5;
        }

        .status-pill {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            color: #fff;
            font-weight: 600;
            text-transform: capitalize;
            font-size: 0.5rem;
        }

        .empty {
            background: #5cb85c;
        }

        .half-full {
            background: #5bc0de;
        }

        .full {
            background: #f0ad4e;
        }

        .overflowing {
            background: #d9534f;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2>Waste Bin Map — Chuka Region</h2>
            <p>Click a bin marker to view or update its status. You can also click on the map to auto-fill coordinates in your report form.</p>

            <div id="map" class="map-container"></div>

            <div class="report-card">
                <h3><i class="ri-map-pin-2-line"></i> Submit Waste Bin Report</h3>
                <form action="submit_report.php" method="POST" enctype="multipart/form-data" class="report-form">
                    <label for="status">Bin Status:</label>
                    <select name="status" id="status" required>
                        <option value="">-- Select Bin Status --</option>
                        <option value="empty">Empty</option>
                        <option value="half-full">Half Full</option>
                        <option value="full">Full</option>
                        <option value="overflowing">Overflowing</option>
                    </select>

                    <label for="location">Location:</label>
                    <input type="text" name="location" id="location" placeholder="Click on map to auto-fill coordinates" required>

                    <label for="image">Upload Image (optional):</label>
                    <input type="file" name="image" id="image" accept="image/*">

                    <button type="submit"><i class="ri-send-plane-fill"></i> Submit Report</button>
                </form>
            </div>

            <hr style="margin:2rem 0;">

            <h2>My Recent Reports</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require_once '../../config.php';
                    $user_id = $_SESSION['user']['id'];
                    $stmt = $pdo->prepare('SELECT * FROM waste_reports WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
                    $stmt->execute([$user_id]);
                    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($reports && count($reports) > 0):
                        foreach ($reports as $i => $r):
                            $status = strtolower($r['status']);
                    ?>
                            <tr>
                                <td><?php echo ($i + 1); ?></td>
                                <td><span class="status-pill <?php echo $status; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($r['location']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                            </tr>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <tr>
                            <td colspan="4">No reports submitted yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialize map
        const map = L.map('map').setView([-0.3320, 37.6448], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Fetch bin data from database
        fetch('../collector/get_bins.php')
            .then(response => response.json())
            .then(bins => {
                const colors = {
                    "empty": "green",
                    "half-full": "blue",
                    "full": "orange",
                    "overflowing": "red"
                };

                bins.forEach(bin => {
                    if (!bin.latitude || !bin.longitude) return;
                    const lat = parseFloat(bin.latitude);
                    const lng = parseFloat(bin.longitude);
                    const color = colors[bin.status] || "gray";

                    const marker = L.circleMarker([lat, lng], {
                        color,
                        radius: 9,
                        fillOpacity: 0.9
                    }).addTo(map);

                    marker.bindPopup(`<b>${bin.location}</b><br>Status: ${bin.status}`);
                });

            })
            .catch(err => console.error('Error loading bins:', err));


        function updateBin(id) {
            const status = document.getElementById(`status-${id}`).value;
            fetch('update_bin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}&status=${status}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) alert(`✅ Bin #${id} updated to ${status}`);
                });
        }


        // Click to fill coordinates
        map.on('click', e => {
            const {
                lat,
                lng
            } = e.latlng;
            document.getElementById('location').value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        });
    </script>
</body>

</html>