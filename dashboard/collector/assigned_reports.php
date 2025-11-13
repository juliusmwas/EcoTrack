<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once '../../config.php';
require_once 'logactivity.php';

// ✅ Handle AJAX status update — auto-saves to DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['status'])) {
    $report_id = (int)$_POST['report_id'];
    $status = trim($_POST['status']);

    $stmt = $pdo->prepare("UPDATE waste_reports SET assignment_status = ? WHERE id = ?");
    $stmt->execute([$status, $report_id]);

    $collector_id = $_SESSION['user']['id'];
    logActivity($collector_id, "Updated report #{$report_id} assignment status to '{$status}'");

    echo json_encode(["success" => true, "newStatus" => $status]);
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --success: #5cb85c;
            --warning: #f0ad4e;
            --info: #5bc0de;
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
            justify-content: flex-end;
            align-items: center;
        }

        select {
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.85rem;
        }

        .notification {
            background: #d4edda;
            color: #155724;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 3px 8px var(--shadow);
            display: none;
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

            <div id="notif" class="notification">
                ✅ Report status updated successfully.
            </div>

            <div class="cards-grid" id="reportsContainer">
                <?php
                $collector_id = $_SESSION['user']['id'];
                $stmt = $pdo->prepare("SELECT * FROM waste_reports WHERE collector_id = ? ORDER BY created_at DESC");
                $stmt->execute([$collector_id]);
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($reports) {
                    foreach ($reports as $r) {
                        $status = strtolower(trim($r['assignment_status']));
                        $statusClass = str_replace(' ', '-', $status);
                        $date = date('M d, Y', strtotime($r['created_at']));

                        echo "
                        <div class='report-card' data-id='{$r['id']}'>
                            <div class='report-header'>
                                <h3><i class='ri-map-pin-line'></i> " . htmlspecialchars($r['location']) . "</h3>
                                <span class='status-badge {$statusClass}' id='status-badge-{$r['id']}'>" . ucfirst($status) . "</span>
                            </div>
                            <div class='report-body'>
                                <p><i class='ri-calendar-line'></i> Reported on: <b>{$date}</b></p>
                                <p><i class='ri-information-line'></i> Status: <b id='status-text-{$r['id']}'>" . ucfirst($status) . "</b></p>
                            </div>
                            <div class='report-footer'>
                                <select id='status-select-{$r['id']}' onchange='autoUpdateStatus({$r['id']})'>
                                    <option value='pending' " . ($status == 'pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='in progress' " . ($status == 'in progress' ? 'selected' : '') . ">In Progress</option>
                                    <option value='resolved' " . ($status == 'resolved' ? 'selected' : '') . ">Resolved</option>
                                </select>
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

    <script>
        async function autoUpdateStatus(id) {
            const newStatus = document.getElementById(`status-select-${id}`).value;

            try {
                const res = await axios.post('', new URLSearchParams({
                    report_id: id,
                    status: newStatus
                }));

                if (res.data.success) {
                    const formattedStatus = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    document.getElementById(`status-badge-${id}`).textContent = formattedStatus;
                    document.getElementById(`status-text-${id}`).textContent = formattedStatus;

                    const badge = document.getElementById(`status-badge-${id}`);
                    badge.className = 'status-badge ' + newStatus.replace(' ', '-');

                    const notif = document.getElementById('notif');
                    notif.style.display = 'block';
                    setTimeout(() => notif.style.display = 'none', 2000);

                    // 🔄 Update collector dashboard counts automatically if available
                    if (window.updateDashboardCounts) {
                        updateDashboardCounts();
                    }
                }
            } catch (error) {
                alert('❌ Failed to update status. Please try again.');
                console.error(error);
            }
        }
    </script>
</body>

</html>