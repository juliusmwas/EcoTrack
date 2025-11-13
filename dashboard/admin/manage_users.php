<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once "../../config.php";

// Fetch all users
$users = $pdo->query("SELECT id, fullname AS name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch unassigned waste reports (status = 'pending' or collector_id IS NULL)
$reports = $pdo->query("SELECT id, location, assignment_status FROM waste_reports WHERE collector_id IS NULL OR assignment_status='pending'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | EcoTrack Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
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
            margin-bottom: 0.5rem;
        }

        .search-bar {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-bar input,
        .search-bar select {
            padding: 0.6rem 0.8rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
            flex: 1;
            min-width: 150px;
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.2rem;
        }

        .user-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
            padding: 1rem;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .user-card:hover {
            transform: translateY(-3px);
        }

        .user-info h4 {
            margin: 0;
            color: var(--primary);
        }

        .user-info p {
            margin: 0.3rem 0;
            color: #555;
            font-size: 0.9rem;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #fff;
            display: inline-block;
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

        .action-btn {
            margin-top: 0.8rem;
            background: var(--accent);
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .action-btn:hover {
            background: #2fa572;
        }

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

        @media(max-width:768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .users-grid {
                grid-template-columns: 1fr;
            }

            .search-bar {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-user-settings-line"></i> Manage Users</h2>
            <p>View and manage all users in the EcoTrack system.</p>

            <!-- Search / Filter -->
            <div class="search-bar">
                <input type="text" id="searchName" placeholder="Search by Name">
                <input type="text" id="searchEmail" placeholder="Search by Email">
                <select id="searchRole">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="collector">Collector</option>
                    <option value="resident">Resident</option>
                </select>
            </div>

            <div class="users-grid" id="usersGrid">
                <?php foreach ($users as $user): ?>
                    <div class="user-card" data-name="<?= strtolower($user['name']) ?>" data-email="<?= strtolower($user['email']) ?>" data-role="<?= strtolower($user['role']) ?>">
                        <div class="user-info">
                            <h4><?= htmlspecialchars($user['name']) ?></h4>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Role:</strong> <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></p>
                            <p><strong>Registered:</strong> <?= date("M d, Y", strtotime($user['created_at'])) ?></p>
                        </div>
                        <div>
                            <?php if ($user['role'] == 'collector'): ?>
                                <button class="action-btn assign-btn" data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>">Assign Task</button>
                            <?php else: ?>
                                <button class="action-btn" style="background: gray;">View</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                        <option value="<?= $report['id'] ?>">#<?= $report['id'] ?> - <?= htmlspecialchars($report['location']) ?> (<?= ucfirst($report['assignment_status']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="action-btn">Assign</button>
                <button type="button" class="close-btn" id="closeModal">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Modal handling
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

        // Inline search/filter
        const searchName = document.getElementById('searchName');
        const searchEmail = document.getElementById('searchEmail');
        const searchRole = document.getElementById('searchRole');
        const usersGrid = document.getElementById('usersGrid');
        const userCards = usersGrid.querySelectorAll('.user-card');

        function filterUsers() {
            const nameVal = searchName.value.toLowerCase();
            const emailVal = searchEmail.value.toLowerCase();
            const roleVal = searchRole.value;

            userCards.forEach(card => {
                const matchName = card.dataset.name.includes(nameVal);
                const matchEmail = card.dataset.email.includes(emailVal);
                const matchRole = roleVal === '' || card.dataset.role === roleVal;
                card.style.display = (matchName && matchEmail && matchRole) ? 'flex' : 'none';
            });
        }

        searchName.addEventListener('input', filterUsers);
        searchEmail.addEventListener('input', filterUsers);
        searchRole.addEventListener('change', filterUsers);
    </script>
</body>

</html>