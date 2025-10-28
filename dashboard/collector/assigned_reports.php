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

    // Update assignment status for that report
    $stmt = $pdo->prepare("UPDATE waste_reports SET assignment_status = ? WHERE id = ?");
    $stmt->execute([$status, $report_id]);

    // Log collector action
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

        .reports-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
            padding: 1.5rem;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px var(--shadow);
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            background: #fff;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: center;
            white-space: nowrap;
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

        .in-progress,
        .in\ progress {
            background: #5bc0de;
        }

        .resolved {
            background: #5cb85c;
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
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        button:hover {
            background: #37a471;
        }

        @media (max-width: 600px) {

            th,
            td {
                font-size: 0.85rem;
                padding: 0.7rem;
            }

            select,
            button {
                font-size: 0.8rem;
                padding: 0.3rem 0.6rem;
            }

            .reports-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-list-check-2"></i> My Assigned Reports</h2>

            <?php if (isset($_GET['updated'])): ?>
                <div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;">
                    ✅ Report assignment status updated successfully.
                </div>
            <?php endif; ?>

            <div class="reports-container">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Location</th>
                                <th>Assignment Status</th>
                                <th>Reported On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $collector_id = $_SESSION['user']['id'];

                            // ✅ Show all reports assigned to this collector (regardless of status)
                            $stmt = $pdo->prepare("
                                SELECT * 
                                FROM waste_reports 
                                WHERE collector_id = ?
                                ORDER BY created_at DESC
                            ");
                            $stmt->execute([$collector_id]);
                            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($reports) {
                                foreach ($reports as $i => $r) {
                                    $status = strtolower($r['assignment_status']);
                                    $statusClass = str_replace(' ', '-', $status);

                                    echo "<tr>
                                        <td>" . ($i + 1) . "</td>
                                        <td>" . htmlspecialchars($r['location']) . "</td>
                                        <td><span class='status-pill {$statusClass}'>" . ucfirst($status) . "</span></td>
                                        <td>" . date('Y-m-d', strtotime($r['created_at'])) . "</td>
                                        <td>
                                            <form method='POST' style='display:flex;gap:0.3rem;justify-content:center;'>
                                                <input type='hidden' name='report_id' value='{$r['id']}'>
                                                <select name='status'>
                                                    <option value='pending' " . ($status == 'pending' ? 'selected' : '') . ">Pending</option>
                                                    <option value='in progress' " . ($status == 'in progress' ? 'selected' : '') . ">In Progress</option>
                                                    <option value='resolved' " . ($status == 'resolved' ? 'selected' : '') . ">Resolved</option>
                                                </select>
                                                <button type='submit'><i class='ri-check-line'></i></button>
                                            </form>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No assigned reports found.</td></tr>";
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