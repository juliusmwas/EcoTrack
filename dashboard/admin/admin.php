<?php
session_start();

$timeout_duration = 1800; // 30 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once "../../config.php";

// Get total users by role
$totalCollectors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='collector'")->fetchColumn();
$totalResidents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='resident'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Get report stats
$totalReports = $pdo->query("SELECT COUNT(*) FROM waste_reports")->fetchColumn();
$statusCounts = $pdo->query("
    SELECT assignment_status, COUNT(*) as count 
    FROM waste_reports 
    GROUP BY assignment_status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Prepare chart data
$statuses = ['pending', 'in progress', 'resolved'];
$chartData = [];
foreach ($statuses as $s) {
    $chartData[] = isset($statusCounts[$s]) ? $statusCounts[$s] : 0;
}

// Recent reports
$recentReports = $pdo->query("
    SELECT id, location, assignment_status, created_at 
    FROM waste_reports 
    ORDER BY created_at DESC 
    LIMIT 6
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
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.15);
            --bg: #f5f7f8;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: var(--bg);
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
        }

        h2 {
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #fff;
            padding: 1.2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 10px var(--shadow);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            margin: 0.3rem 0;
            font-size: 1.5rem;
            color: var(--primary);
        }

        .chart-container {
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .chart-wrapper {
            max-width: 200px;
            width: 100%;
            margin-bottom: 1rem;
        }

        /* Smaller doughnut size */
        .progress-bar-wrapper {
            margin: 0.5rem 0;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 18px;
        }

        .progress-bar {
            height: 100%;
            text-align: right;
            padding-right: 5px;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 18px;
            transition: width 0.6s ease;
        }

        .status-pending {
            background: #f0ad4e;
        }

        .status-in-progress {
            background: #17a2b8;
        }

        .status-resolved {
            background: #5cb85c;
        }

        .recent-reports {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .report-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
            padding: 1rem;
            transition: transform 0.2s;
        }

        .report-card:hover {
            transform: translateY(-3px);
        }

        .report-card h4 {
            margin: 0 0 0.3rem 0;
            font-size: 1.1rem;
            color: var(--primary);
        }

        .report-card p {
            margin: 0.2rem 0;
            color: #555;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #fff;
            text-transform: capitalize;
        }

        @media (max-width:768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .recent-reports {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-dashboard-line"></i> Admin Dashboard</h2>
            <p>Welcome back, Admin! Here's the current overview.</p>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card"><i class="ri-map-pin-line"></i>
                    <h3><?= $totalReports ?></h3>
                    <p>Total Reports</p>
                </div>
                <div class="stat-card"><i class="ri-user-settings-line"></i>
                    <h3><?= $totalCollectors ?></h3>
                    <p>Collectors</p>
                </div>
                <div class="stat-card"><i class="ri-user-line"></i>
                    <h3><?= $totalResidents ?></h3>
                    <p>Residents</p>
                </div>
                <div class="stat-card"><i class="ri-group-line"></i>
                    <h3><?= $totalUsers ?></h3>
                    <p>Total Users</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <h3><i class="ri-bar-chart-line"></i> Reports by Status</h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
                <div class="progress-bars" style="width:100%;">
                    <?php
                    $total = array_sum($chartData);
                    foreach ($statuses as $index => $s):
                        $count = $chartData[$index];
                        $percent = $total > 0 ? round(($count / $total) * 100) : 0;
                    ?>
                        <div style="margin:0.5rem 0;"><strong><?= ucfirst($s) ?>:</strong> <?= $count ?> reports</div>
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar status-<?= str_replace(' ', '-', $s) ?>" style="width: <?= $percent ?>%;"><?= $percent ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Reports -->
            <h3><i class="ri-time-line"></i> Recent Reports</h3>
            <div class="recent-reports">
                <?php foreach ($recentReports as $r): ?>
                    <div class="report-card">
                        <h4>Report #<?= $r['id'] ?></h4>
                        <p><strong>Location:</strong> <?= htmlspecialchars($r['location']) ?></p>
                        <p><strong>Date:</strong> <?= date("M d, Y H:i", strtotime($r['created_at'])) ?></p>
                        <p><strong>Status:</strong>
                            <span class="status-badge status-<?= str_replace(' ', '-', $r['assignment_status']) ?>">
                                <?= ucfirst($r['assignment_status']) ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved'],
                datasets: [{
                    label: 'Reports by Status',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: ['#f0ad4e', '#17a2b8', '#5cb85c'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    </script>

</body>

</html>