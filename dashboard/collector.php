<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard | EcoTrack</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
            --light-bg: #F5F8F7;
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        p {
            color: #444;
            margin-bottom: 2rem;
        }

        /* --- Dashboard Cards --- */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px var(--shadow);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card i {
            font-size: 2rem;
            color: var(--accent);
            background: #e8f7f3;
            padding: 0.8rem;
            border-radius: 50%;
        }

        .card-content h3 {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        .card-content span {
            color: #666;
            font-size: 0.9rem;
        }

        /* --- Recent Reports Table --- */
        .reports-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px var(--shadow);
            padding: 1.5rem;
        }

        .reports-section h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px var(--shadow);
            -webkit-overflow-scrolling: touch;
            /* smooth scroll on mobile */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            /* ensures scroll activates on narrow screens */
            background: #fff;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: center;
            white-space: nowrap;
            /* prevents cell wrapping on mobile */
        }

        thead {
            background: var(--primary);
            color: #fff;
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
            font-size: 0.85rem;
        }

        .pending {
            background: #f0ad4e;
        }

        .in-progress {
            background: #5bc0de;
        }

        .resolved {
            background: #5cb85c;
        }

        @media (max-width:600px) {
            h2 {
                font-size: 1.4rem;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }


            .card-content h3 {
                font-size: 1.2rem;
            }

            th,
            td {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <h2>Collector Dashboard</h2>
            <p>Welcome back! Here’s an overview of your assigned waste collection reports.</p>

            <!-- Summary Cards -->
            <div class="dashboard-cards">
                <?php
                // Fetch stats from DB
                $collector_id = $_SESSION['user']['id'];
                $total = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE assigned_to=?");
                $total->execute([$collector_id]);
                $total_reports = $total->fetchColumn();

                $in_progress = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE assigned_to=? AND status='in progress'");
                $in_progress->execute([$collector_id]);
                $in_progress_count = $in_progress->fetchColumn();

                $resolved = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE assigned_to=? AND status='resolved'");
                $resolved->execute([$collector_id]);
                $resolved_count = $resolved->fetchColumn();
                ?>
                <div class="card">
                    <i class="ri-file-list-3-line"></i>
                    <div class="card-content">
                        <h3><?php echo $total_reports; ?></h3>
                        <span>Total Reports</span>
                    </div>
                </div>
                <div class="card">
                    <i class="ri-loader-2-line"></i>
                    <div class="card-content">
                        <h3><?php echo $in_progress_count; ?></h3>
                        <span>In Progress</span>
                    </div>
                </div>
                <div class="card">
                    <i class="ri-checkbox-circle-line"></i>
                    <div class="card-content">
                        <h3><?php echo $resolved_count; ?></h3>
                        <span>Resolved</span>
                    </div>
                </div>
            </div>

            <!-- Recent Assigned Reports -->
            <div class="reports-section">
                <h3><i class="ri-time-line"></i> Recent Assigned Reports</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Reported On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM waste_reports WHERE assigned_to=? ORDER BY created_at DESC LIMIT 8");
                            $stmt->execute([$collector_id]);
                            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($reports) {
                                foreach ($reports as $i => $r) {
                                    $status = strtolower($r['status']);
                                    echo "<tr>
                                        <td>" . ($i + 1) . "</td>
                                        <td>" . htmlspecialchars($r['location']) . "</td>
                                        <td><span class='status-pill $status'>" . ucfirst($status) . "</span></td>
                                        <td>" . date('Y-m-d', strtotime($r['created_at'])) . "</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No assigned reports found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>