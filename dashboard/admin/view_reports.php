<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once "../../config.php";

// Fetch all waste reports
$query = "SELECT wr.id, wr.location, wr.status, wr.image, wr.created_at,
                 u.fullname AS reporter_name,
                 c.fullname AS collector_name
          FROM waste_reports wr
          LEFT JOIN users u ON wr.user_id = u.id
          LEFT JOIN users c ON wr.collector_id = c.id
          ORDER BY wr.created_at DESC";

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
            background: var(--light-bg);
            font-family: "Poppins", sans-serif;
            margin: 0;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
            transition: 0.3s;
        }

        h2 {
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-container {
            background: #fff;
            padding: 1.5rem;
            border-radius: 14px;
            box-shadow: 0 3px 12px var(--shadow);
            margin-top: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }

        thead {
            background: var(--primary);
            color: #fff;
        }

        th {
            font-weight: 500;
            padding: 0.9rem;
            font-size: 0.9rem;
        }

        td {
            padding: 0.85rem;
            border-bottom: 1px solid #e6e6e6;
            font-size: 0.88rem;
        }

        tr:hover td {
            background: #eef8f7;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
        }

        .pending {
            background: #6c757d;
        }

        .assigned {
            background: #0ea5e9;
        }

        .in-progress {
            background: #f59e0b;
        }

        .resolved {
            background: #22c55e;
        }

        .report-img {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #e3e3e3;
            transition: 0.3s;
        }

        .report-img:hover {
            transform: scale(1.1);
            cursor: pointer;
        }

        small {
            display: block;
            color: #555;
            font-size: 0.75rem;
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 700px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 1rem;
                background: #fff;
                padding: 1rem;
                border-radius: 12px;
                box-shadow: 0 2px 6px var(--shadow);
            }

            td {
                border: none;
                display: flex;
                justify-content: space-between;
                padding: 0.4rem 0;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-right: 12px;
            }

            .report-img {
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

            <h2><i class="ri-file-list-line"></i> Waste Reports Overview</h2>
            <p style="color:#555;">All waste reports submitted and their current statuses.</p>

            <div class="table-container">
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
                                    <td data-label="Reporter">
                                        <?= htmlspecialchars($report['reporter_name'] ?? 'N/A') ?>
                                    </td>
                                    <td data-label="Location"><?= htmlspecialchars($report['location']) ?></td>
                                    <td data-label="Status">
                                        <span class="status <?= htmlspecialchars($report['status']) ?>">
                                            <?= htmlspecialchars($report['status']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Collector">
                                        <?= htmlspecialchars($report['collector_name'] ?? 'Unassigned') ?>
                                    </td>
                                    <td data-label="Image">
                                        <?php if ($report['image']): ?>
                                            <img src="../uploads/<?= htmlspecialchars($report['image']) ?>" class="report-img" alt="Report Image">
                                        <?php else: ?>
                                            <small>No image</small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Reported On">
                                        <?= date("M d, Y H:i", strtotime($report['created_at'])) ?>
                                    </td>
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