<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../../config.php";

// Fetch all waste reports 
$query = "
    SELECT wr.id, wr.location, wr.status, wr.image, wr.created_at,
           u.fullname AS reporter_name, 
           c.fullname AS collector_name
    FROM waste_reports wr
    LEFT JOIN users u ON wr.reported_by = u.id
    LEFT JOIN users c ON wr.collector_id = c.id
    ORDER BY wr.created_at DESC
";


$stmt = $pdo->query($query);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports | EcoTrack Admin</title>
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
            margin: 0;
            background: var(--light-bg);
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

        .status {
            padding: 4px 8px;
            border-radius: 6px;
            color: #fff;
            font-size: 0.85rem;
            text-transform: capitalize;
        }

        .pending {
            background: gray;
        }

        .assigned {
            background: #17a2b8;
        }

        .in-progress {
            background: #ffc107;
        }

        .resolved {
            background: #28a745;
        }

        img.report-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #ccc;
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

            img.report-img {
                width: 50px;
                height: 50px;
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
                <h2><i class="ri-file-list-line"></i> Waste Reports Overview</h2>
                <p>All waste reports submitted by residents and their current statuses.</p>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Collector</th>
                            <th>Image</th>
                            <th>Reported On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reports): ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td data-label="ID">#<?= htmlspecialchars($report['id']) ?></td>
                                    <td data-label="Reporter"><?= htmlspecialchars($report['reporter_name'] ?? 'N/A') ?></td>
                                    <td data-label="Location"><?= htmlspecialchars($report['location']) ?></td>
                                    <td data-label="Status">
                                        <span class="status <?= htmlspecialchars($report['status']) ?>">
                                            <?= htmlspecialchars($report['status']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Collector"><?= htmlspecialchars($report['collector_name'] ?? 'Unassigned') ?></td>
                                    <td data-label="Image">
                                        <?php if ($report['image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($report['image']) ?>" alt="Report Image" class="report-img">
                                        <?php else: ?>
                                            <em>No image</em>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Reported On"><?= date("M d, Y H:i", strtotime($report['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>