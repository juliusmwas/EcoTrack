<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once '../../config.php';
require_once 'logactivity.php';

// ✅ Handle status update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['status'])) {
    $report_id = $_POST['report_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE waste_reports SET assignment_status = ? WHERE id = ?");
    $stmt->execute([$status, $report_id]);

    $collector_id = $_SESSION['user']['id'];
    logActivity($collector_id, "Updated report #{$report_id} assignment status to '{$status}'");

    header("Location: assigned_reports.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assigned Reports | EcoTrack</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --warning: #f0ad4e;
            --info: #5bc0de;
            --success: #5cb85c;
            --shadow: rgba(0, 0, 0, 0.15);
            --bg: #f5f7f8;
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

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
        }

        .report-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow);
            padding: 1.2rem 1.4rem;
            transition: 0.3s ease;
            border-left: 6px solid var(--primary);
        }

        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px var(--shadow);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-header h3 {
            font-size: 1rem;
            margin: 0;
            color: #333;
        }

        .status-badge {
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #fff;
            text-transform: capitalize;
            font-weight: 600;
        }

        .pending {
            background: var(--warning);
        }

        .in-progress,
        .in\ progress {
            background: var(--info);
        }

        .resolved {
            background: var(--success);
        }

        .report-body {
            margin-top: 0.9rem;
            color: #555;
        }

        .report-body p {
            margin: 0.4rem 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .report-footer {
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        select {
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.85rem;
        }

        button {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 0.45rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        button:hover {
            background: #38a876;
        }

        .notification {
            background: #d4edda;
            color: #155724;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 3px 8px var(--shadow);
        }

        .empty {
            text-align: center;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            color: #888;
            box-shadow: 0 4px 10px var(--shadow);
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-file-list-line"></i> My Assigned Reports</h2>

            <?php if (isset($_GET['updated'])): ?>
                <div class="notification">
                    ✅ Report assignment status updated successfully.
                </div>
            <?php endif; ?>

            <div class="cards-grid">
                <?php
                $collector_id = $_SESSION['user']['id'];

                $stmt = $pdo->prepare("
                SELECT * 
                FROM waste_reports 
                WHERE collector_id = ?
                ORDER BY created_at DESC
            ");
                $stmt->execute([$collector_id]);
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($reports) {
                    foreach ($reports as $r) {
                        $status = strtolower($r['assignment_status']);
                        $statusClass = str_replace(' ', '-', $status);
                        $date = date('M d, Y', strtotime($r['created_at']));
                        echo "
                    <div class='report-card' style='border-left-color:" .
                            ($status == 'pending' ? '#f0ad4e' : ($status == 'in progress' ? '#5bc0de' : '#5cb85c')) . ";'>
                        <div class='report-header'>
                            <h3><i class='ri-map-pin-line'></i> " . htmlspecialchars($r['location']) . "</h3>
                            <span class='status-badge {$statusClass}'>" . ucfirst($status) . "</span>
                        </div>
                        <div class='report-body'>
                            <p><i class='ri-calendar-line'></i> Reported on: <b>{$date}</b></p>
                            <p><i class='ri-information-line'></i> Status: <b>" . ucfirst($status) . "</b></p>
                        </div>
                        <div class='report-footer'>
                            <form method='POST' style='display:flex;gap:0.5rem;align-items:center;'>
                                <input type='hidden' name='report_id' value='{$r['id']}'>
                                <select name='status'>
                                    <option value='pending' " . ($status == 'pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='in progress' " . ($status == 'in progress' ? 'selected' : '') . ">In Progress</option>
                                    <option value='resolved' " . ($status == 'resolved' ? 'selected' : '') . ">Resolved</option>
                                </select>
                                <button type='submit'><i class='ri-check-line'></i> Update</button>
                            </form>
                        </div>
                    </div>";
                    }
                } else {
                    echo "<div class='empty'><i class='ri-folder-info-line'></i> No assigned reports found.</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>