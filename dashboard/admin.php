<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../config.php";

// Get total users by role
$totalCollectors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='collector'")->fetchColumn();
$totalResidents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='resident'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get report stats
$totalReports = $pdo->query("SELECT COUNT(*) FROM waste_reports")->fetchColumn();
$statusCounts = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM waste_reports 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Chart data
$statuses = ['pending', 'in-progress', 'resolved'];
$data = [];
foreach ($statuses as $s) {
    $data[] = isset($statusCounts[$s]) ? $statusCounts[$s] : 0;
}

// Recent reports
$recentReports = $pdo->query("
    SELECT id, location, status, created_at 
    FROM waste_reports 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EcoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
            --light-bg: #F5F8F7;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .dashboard-container {
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow);
            max-width: 1200px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--light-bg);
            padding: 1.2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            font-size: 1.3rem;
            color: var(--primary);
            margin: 0.3rem 0;
        }

        .chart-section {
            margin-top: 2rem;
            text-align: center;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        .recent-reports {
            margin-top: 2.5rem;
        }

        .recent-reports table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-reports th,
        .recent-reports td {
            padding: 0.8rem;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .recent-reports th {
            background: var(--primary);
            color: #fff;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            color: #fff;
            font-size: 0.85rem;
        }

        .status-pending {
            background: gray;
        }

        .status-in-progress {
            background: #17a2b8;
        }

        .status-resolved {
            background: #28a745;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
                margin: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .recent-reports table {
                font-size: 0.85rem;
            }

            th,
            td {
                padding: 0.6rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
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
            <div class="dashboard-container">
                <h2><i class="ri-dashboard-line"></i> Admin Dashboard</h2>
                <p>Welcome back, Admin! Here’s the current overview of EcoTrack’s performance.</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="ri-map-pin-line"></i>
                        <h3><?= $totalReports ?></h3>
                        <p>Total Reports</p>
                    </div>
                    <div class="stat-card">
                        <i class="ri-user-settings-line"></i>
                        <h3><?= $totalCollectors ?></h3>
                        <p>Collectors</p>
                    </div>
                    <div class="stat-card">
                        <i class="ri-user-line"></i>
                        <h3><?= $totalResidents ?></h3>
                        <p>Residents</p>
                    </div>
                    <div class="stat-card">
                        <i class="ri-group-line"></i>
                        <h3><?= $totalUsers ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="chart-section">
                    <h3><i class="ri-bar-chart-2-line"></i> Report Status Overview</h3>
                    <canvas id="statusChart"></canvas>
                </div>

                <div class="recent-reports">
                    <h3><i class="ri-time-line"></i> Recent Reports</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $r): ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['location']) ?></td>
                                    <td><span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                                    <td><?= date("M d, Y H:i", strtotime($r['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In-Progress', 'Resolved'],
                datasets: [{
                    label: 'Report Status',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: ['gray', '#17a2b8', '#28a745'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>