<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../config.php";

// Fetch recent activities
$query = "
    SELECT 
        l.id, 
        u.fullname AS user_name, 
        u.role, 
        l.action, 
        l.created_at 
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
";

$stmt = $pdo->query($query);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs | EcoTrack Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">

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

        .role-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #fff;
            text-transform: capitalize;
        }

        .role-admin {
            background: #6c63ff;
        }

        .role-collector {
            background: #17a2b8;
        }

        .role-resident {
            background: #28a745;
        }

        .action {
            color: #333;
            font-weight: 500;
        }

        .timestamp {
            font-size: 0.9rem;
            color: gray;
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
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <div class="container">
                <h2><i class="ri-time-line"></i> Activity Logs</h2>
                <p>Monitor all user actions and system events for accountability.</p>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td data-label="ID">#<?= htmlspecialchars($log['id']) ?></td>
                                    <td data-label="User"><?= htmlspecialchars($log['user_name'] ?? 'N/A') ?></td>
                                    <td data-label="Role">
                                        <span class="role-badge role-<?= htmlspecialchars($log['role']) ?>">
                                            <?= ucfirst($log['role'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td data-label="Action" class="action"><?= htmlspecialchars($log['action']) ?></td>
                                    <td data-label="Date" class="timestamp">
                                        <?= date("M d, Y H:i", strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">No activity logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>