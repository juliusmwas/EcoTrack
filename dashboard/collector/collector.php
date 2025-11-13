<?php
session_start();

// --- SESSION TIMEOUT HANDLING ---
$timeout_duration = 1800; // 30 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// --- CHECK LOGIN & ROLE ---
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config.php';

// --- HELPER: safe display ---
function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// --- GET USER DATA ---
$user_name = $_SESSION['user']['name'] ?? 'Collector';
$collector_id = $_SESSION['user']['id'] ?? 0;

// --- STATS ---
$stmt_total = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE collector_id = ?");
$stmt_total->execute([$collector_id]);
$total_reports = (int) $stmt_total->fetchColumn();

$stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE collector_id = ? AND assignment_status = 'pending'");
$stmt_pending->execute([$collector_id]);
$pending_count = (int) $stmt_pending->fetchColumn();

$stmt_in_progress = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE collector_id = ? AND assignment_status = 'in progress'");
$stmt_in_progress->execute([$collector_id]);
$in_progress_count = (int) $stmt_in_progress->fetchColumn();

$stmt_resolved = $pdo->prepare("SELECT COUNT(*) FROM waste_reports WHERE collector_id = ? AND assignment_status = 'Resolved'");
$stmt_resolved->execute([$collector_id]);
$resolved_count = (int) $stmt_resolved->fetchColumn();

// --- CALCULATE PROGRESS ---
if ($total_reports > 0) {
    $progress = round(($resolved_count / $total_reports) * 100, 1);
} else {
    $progress = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard | EcoTrack</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --warning: #F0AD4E;
            --info: #5BC0DE;
            --success: #5CB85C;
            --bg: #F5F8F7;
            --shadow: rgba(0, 0, 0, 0.15);
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
        }

        @media(max-width:768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        h2 {
            color: var(--primary);
            margin-bottom: .5rem;
        }

        .welcome {
            color: #444;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        /* --- Summary Cards --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #1B7F79, #42B883);
            color: #fff;
            border-radius: 15px;
            padding: 1.4rem 1.5rem;
            box-shadow: 0 5px 15px var(--shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: .3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            opacity: 0.9;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: .4rem 0;
        }

        .stat-label {
            opacity: 0.9;
            font-size: .95rem;
        }

        /* --- Progress Section --- */
        .progress-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .progress-chart {
            width: 140px;
            height: 140px;
            position: relative;
        }

        .progress-chart svg {
            transform: rotate(-90deg);
        }

        .progress-chart circle {
            fill: none;
            stroke-width: 12;
            stroke-linecap: round;
        }

        .progress-bg {
            stroke: #eee;
        }

        .progress-value {
            stroke: var(--accent);
            transition: 1s ease;
        }

        .progress-chart span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            color: #333;
        }

        .progress-text {
            flex: 1;
            color: #444;
        }

        .progress-text h3 {
            color: var(--primary);
            margin-bottom: .3rem;
        }

        /* --- Quick Actions --- */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.2rem;
        }

        .action-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: .3s;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-4px);
            background: #f9fdfa;
        }

        .action-card i {
            font-size: 1.8rem;
            color: var(--accent);
            background: #e8f7f3;
            border-radius: 50%;
            padding: .8rem;
        }

        .action-text h4 {
            margin: 0;
            color: var(--primary);
        }

        .action-text span {
            font-size: .85rem;
            color: #777;
        }

        /* --- Activity Summary --- */
        .activity {
            margin-top: 2.5rem;
            background: #fff;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .activity h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .activity p {
            color: #555;
            margin-bottom: .5rem;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-dashboard-line"></i> Collector Dashboard</h2>
            <p class="welcome">Welcome back, <?php echo safe($user_name); ?> 👋. Here’s your latest overview and quick actions.</p>

            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="ri-file-list-3-line"></i>
                    <div>
                        <p class="stat-number"><?php echo $total_reports; ?></p>
                        <p class="stat-label">Total Assigned Reports</p>
                    </div>
                </div>
                <div class="stat-card" style="background:linear-gradient(135deg,#f0ad4e,#f7c46c)">
                    <i class="ri-timer-line"></i>
                    <div>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                        <p class="stat-label">Pending</p>
                    </div>
                </div>
                <div class="stat-card" style="background:linear-gradient(135deg,#5bc0de,#7fd3ec)">
                    <i class="ri-loop-right-line"></i>
                    <div>
                        <p class="stat-number"><?php echo $in_progress_count; ?></p>
                        <p class="stat-label">In Progress</p>
                    </div>
                </div>
                <div class="stat-card" style="background:linear-gradient(135deg,#5cb85c,#7bd77b)">
                    <i class="ri-check-double-line"></i>
                    <div>
                        <p class="stat-number"><?php echo $resolved_count; ?></p>
                        <p class="stat-label">Resolved</p>
                    </div>
                </div>
            </div>

            <!-- Progress Overview -->
            <div class="progress-section">
                <div class="progress-chart">
                    <svg width="140" height="140">
                        <circle class="progress-bg" cx="70" cy="70" r="55"></circle>
                        <circle class="progress-value" cx="70" cy="70" r="55"
                            stroke-dasharray="345"
                            stroke-dashoffset="<?php echo 345 - (345 * $progress / 100); ?>"></circle>
                    </svg>
                    <span><?php echo $progress; ?>%</span>
                </div>
                <div class="progress-text">
                    <h3>Collection Efficiency</h3>
                    <p>You've successfully resolved <strong><?php echo $resolved_count; ?></strong> out of <strong><?php echo $total_reports; ?></strong> assigned reports.</p>
                    <p>Keep up the great work! 🌱</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="window.location.href='assigned_reports.php'">
                    <i class="ri-list-check-2"></i>
                    <div class="action-text">
                        <h4>View Assigned Reports</h4>
                        <span>Manage and update report statuses</span>
                    </div>
                </div>

                <div class="action-card" onclick="window.location.href='profile.php'">
                    <i class="ri-user-settings-line"></i>
                    <div class="action-text">
                        <h4>Profile Settings</h4>
                        <span>Update your account info</span>
                    </div>
                </div>

                <div class="action-card" onclick="window.location.href='../../login.php'">
                    <i class="ri-logout-box-r-line"></i>
                    <div class="action-text">
                        <h4>Logout</h4>
                        <span>End your current session</span>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="activity">
                <h3><i class="ri-bar-chart-2-line"></i> Recent Activity Overview</h3>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($logs) {
                    foreach ($logs as $log) {
                        echo "<p><i class='ri-checkbox-circle-line' style='color:var(--accent);'></i> " . safe($log['action']) . " <small>(" . date('M d, Y H:i', strtotime($log['created_at'])) . ")</small></p>";
                    }
                } else {
                    echo "<p>No recent activity logged.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>