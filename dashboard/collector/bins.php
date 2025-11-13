<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once "../../config.php";

// Fetch all bins (waste reports) from database
$query = "SELECT id, location, status, created_at FROM waste_reports ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$bins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bins | EcoTrack Collector</title>
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
            transition: all 0.3s ease;
        }

        h2 {
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background: var(--primary);
            color: white;
        }

        tr:nth-child(even) {
            background: #f8f8f8;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status.empty {
            background: green;
        }

        .status.half-full {
            background: blue;
        }

        .status.full {
            background: orange;
        }

        .status.overflowing {
            background: red;
        }

        .status.pending {
            background: gray;
        }

        .status.in-progress {
            background: #17a2b8;
        }

        .status.resolved {
            background: #28a745;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-delete-bin-2-line"></i> Waste Bins Overview</h2>
            <p>Here you can see all bins, their status, and when they were reported.</p>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Reported On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bins): ?>
                        <?php foreach ($bins as $bin): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($bin['id']) ?></td>
                                <td><?= htmlspecialchars($bin['location']) ?></td>
                                <td><span class="status <?= htmlspecialchars($bin['status']) ?>"><?= htmlspecialchars($bin['status']) ?></span></td>
                                <td><?= htmlspecialchars(date("M d, Y H:i", strtotime($bin['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:1rem;">No bins found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>