<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once "../../config.php";

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
            transition: all 0.3s ease;
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
            height: 520px;
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

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .modal-content {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
        }

        .modal h3 {
            margin-top: 0;
        }

        .modal select,
        .modal button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        .modal button {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
        }

        .modal button:hover {
            background: var(--accent);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .map-wrapper {
                padding: 1rem;
                margin: 1rem;
            }

            #map {
                height: 400px;
            }

            h2 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <div class="map-wrapper">
                <h2><i class="ri-map-pin-line"></i> Waste Bin Map Overview</h2>
                <p>Click any bin to view and update its details.</p>

                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="binModal">
        <div class="modal-content">
            <h3>Bin Details</h3>
            <p><strong>ID:</strong> <span id="modalBinId"></span></p>
            <p><strong>Current Status:</strong> <span id="modalStatus"></span></p>
            <label for="newStatus">Update Status:</label>
            <select id="newStatus">
                <option value="pending">Pending</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
            </select>
            <button id="updateBtn">Update Status</button>
            <button id="closeModal" style="background:#ccc; color:#000; margin-top:8px;">Close</button>
        </div>
    </div>

    <script>
        const bins = <?php echo json_encode($bins); ?>;
        const map = L.map('map').setView([-0.3320, 37.6448], 14);

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

            const color = colors[bin.status] || "gray";

            const marker = L.circleMarker([lat, lng], {
                color,
                radius: 9,
                fillOpacity: 0.9
            }).addTo(map);

            marker.on('click', () => openModal(bin));
        });

        // Legend
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
            div.innerHTML += '<span style="background:gray"></span>Pending<br>';
            div.innerHTML += '<span style="background:#17a2b8"></span>In Progress<br>';
            div.innerHTML += '<span style="background:#28a745"></span>Resolved';
            return div;
        };
        legend.addTo(map);

        // Modal Logic
        const modal = document.getElementById("binModal");
        const closeModalBtn = document.getElementById("closeModal");
        const updateBtn = document.getElementById("updateBtn");
        let currentBinId = null;

        function openModal(bin) {
            modal.style.display = "flex";
            document.getElementById("modalBinId").textContent = bin.id;
            document.getElementById("modalStatus").textContent = bin.status;
            document.getElementById("newStatus").value = bin.status;
            currentBinId = bin.id;
        }

        closeModalBtn.addEventListener("click", () => modal.style.display = "none");

        updateBtn.addEventListener("click", () => {
            const newStatus = document.getElementById("newStatus").value;
            fetch("update_status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `id=${currentBinId}&status=${newStatus}`
                })
                .then(res => res.text())
                .then(data => {
                    alert(data);
                    modal.style.display = "none";
                    location.reload();
                })
                .catch(err => console.error(err));
        });
    </script>
</body>

</html>