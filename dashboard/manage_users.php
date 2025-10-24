<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once "../config.php";

// Fetch all users
$query = "SELECT id, fullname AS name, email, role, created_at FROM users ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unassigned waste reports (status = 'pending' or collector_id IS NULL)
$reportsQuery = "SELECT id, location, status FROM waste_reports WHERE collector_id IS NULL OR status='pending'";
$reportsStmt = $pdo->query($reportsQuery);
$reports = $reportsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | EcoTrack Admin</title>
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
            margin: 0;
            background: var(--light-bg);
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

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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
            color: white;
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

        .action-btn {
            background: var(--accent);
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.2s;
        }

        .action-btn:hover {
            background: #2fa572;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 10px var(--shadow);
        }

        .modal-content h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
        }

        select,
        button {
            width: 100%;
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 1rem;
        }

        .close-btn {
            background: #e74c3c;
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 600px) {

            .users-table,
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
                <h2><i class="ri-user-settings-line"></i> Manage Users</h2>
                <p>View and manage all users in the EcoTrack system.</p>

                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td data-label="ID">#<?= $user['id'] ?></td>
                                <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
                                <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
                                <td data-label="Role">
                                    <span class="role-badge role-<?= $user['role'] ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td data-label="Registered">
                                    <?= date("M d, Y", strtotime($user['created_at'])) ?>
                                </td>
                                <td data-label="Action">
                                    <?php if ($user['role'] == 'collector'): ?>
                                        <button class="action-btn assign-btn" data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>">Assign Task</button>
                                    <?php else: ?>
                                        <button class="action-btn" style="background: gray;">View</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="assignModal">
        <div class="modal-content">
            <h3>Assign Task to <span id="collectorName"></span></h3>
            <form id="assignForm">
                <input type="hidden" name="collector_id" id="collectorId">
                <select name="report_id" required>
                    <option value="">-- Select Report/Bin --</option>
                    <?php foreach ($reports as $report): ?>
                        <option value="<?= $report['id'] ?>">#<?= $report['id'] ?> - <?= htmlspecialchars($report['location']) ?> (<?= ucfirst($report['status']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="action-btn">Assign</button>
                <button type="button" class="close-btn" id="closeModal">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('assignModal');
        const closeBtn = document.getElementById('closeModal');
        const assignBtns = document.querySelectorAll('.assign-btn');
        const collectorName = document.getElementById('collectorName');
        const collectorId = document.getElementById('collectorId');
        const assignForm = document.getElementById('assignForm');

        assignBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                collectorName.textContent = btn.dataset.name;
                collectorId.value = btn.dataset.id;
                modal.style.display = 'flex';
            });
        });

        closeBtn.addEventListener('click', () => modal.style.display = 'none');

        assignForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(assignForm);

            const response = await fetch('assign_task.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            alert(result);
            modal.style.display = 'none';
            window.location.reload();
        });
    </script>
</body>

</html>