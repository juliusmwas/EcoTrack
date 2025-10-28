<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['location'])) {
    $location = trim($_POST['location']);
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];

    if ($location !== '') {
        $stmt = $pdo->prepare("INSERT INTO bins (location, latitude, longitude) VALUES (?, ?, ?)");
        $stmt->execute([$location, $lat, $lng]);
        $success = "✅ Bin added successfully!";
    }
}


// ✅ Delete Bin
if (isset($_GET['delete'])) {
    $bin_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM bins WHERE id = ?");
    $stmt->execute([$bin_id]);
    $success = "🗑️ Bin deleted successfully!";
}

// ✅ Fetch all bins
$query = "SELECT * FROM bins ORDER BY created_at DESC";
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
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow);
            padding: 2rem;
            max-width: 1200px;
            margin: auto;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f2f9f8;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            color: #fff;
            font-size: 0.85rem;
            text-transform: capitalize;
        }

        .status-active {
            background: #28a745;
        }

        .status-inactive {
            background: gray;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .add-form {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .add-form input {
            flex: 1;
            padding: 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn-add {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.6rem 1.2rem;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #13665f;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }

        /* 📱 Mobile responsiveness */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            th,
            td {
                padding: 0.7rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 600px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                display: none;
            }

            tr {
                background: #fff;
                margin-bottom: 0.8rem;
                border-radius: 10px;
                box-shadow: 0 2px 6px var(--shadow);
                padding: 0.8rem;
            }

            td {
                border: none;
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                color: var(--primary);
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <div class="container">
                <h2><i class="ri-delete-bin-2-line"></i> Manage Waste Bins</h2>
                <p>Add, view, and delete waste collection bins within your system.</p>

                <?php if (!empty($success)): ?>
                    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Add Bin Form -->
                <form method="POST" class="add-form">
                    <input type="text" name="location" placeholder="Enter bin location name..." required>
                    <input type="text" name="latitude" id="latitude" placeholder="Latitude" required>
                    <input type="text" name="longitude" id="longitude" placeholder="Longitude" required>
                    <button type="submit" class="btn-add"><i class="ri-add-circle-line"></i> Add Bin</button>
                </form>

                <!-- Small map for selecting bin location -->
                <div id="selectMap" style="height:300px; border-radius:10px; margin-top:15px;"></div>

                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                <script>
                    const selectMap = L.map('selectMap').setView([-0.3320, 37.6448], 14);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(selectMap);

                    let marker;
                    selectMap.on('click', e => {
                        const {
                            lat,
                            lng
                        } = e.latlng;
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);

                        if (marker) selectMap.removeLayer(marker);
                        marker = L.marker([lat, lng]).addTo(selectMap)
                            .bindPopup(`Selected: ${lat.toFixed(5)}, ${lng.toFixed(5)}`).openPopup();
                    });
                </script>


                <!-- Bin Table -->
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bins): ?>
                            <?php foreach ($bins as $bin): ?>
                                <tr>
                                    <td data-label="ID">#<?= htmlspecialchars($bin['id']) ?></td>
                                    <td data-label="Location"><?= htmlspecialchars($bin['location']) ?></td>
                                    <td data-label="Status">
                                        <span class="status-badge status-<?= strtolower($bin['status'] ?? 'active') ?>">
                                            <?= ucfirst($bin['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td data-label="Created At"><?= date("M d, Y H:i", strtotime($bin['created_at'])) ?></td>
                                    <td data-label="Action">
                                        <a href="?delete=<?= $bin['id'] ?>"
                                            onclick="return confirm('Are you sure you want to delete this bin?')">
                                            <button class="btn-delete"><i class="ri-delete-bin-line"></i> Delete</button>
                                        </a>
                                    </td>
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
    </div>
</body>

</html>