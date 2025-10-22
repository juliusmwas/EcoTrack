<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
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
    <title>My Reports | EcoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        /* --- Main Layout Adjustment --- */
        .main-content {
            margin-left: 230px;
            /* same width as your sidebar */
            padding: 2rem;
            background: var(--light-bg);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Adjust for mobile view */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        .reports-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px var(--shadow);
            padding: 2rem;
            margin-top: 2rem;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px var(--shadow);
            overflow: hidden;
        }

        th,
        td {
            padding: 0.9rem;
            text-align: center;
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
            font-size: 0.9rem;
        }

        .empty {
            background: #5cb85c;
        }

        .half-full {
            background: #5bc0de;
        }

        .full {
            background: #f0ad4e;
        }

        .overflowing {
            background: #d9534f;
        }

        .filter-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 48%;
            margin-top: 0.5rem;
        }

        @media(max-width:600px) {

            .filter-bar input,
            .filter-bar select {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include 'navbar.php'; ?>

        <div class="main-content">
            <div class="reports-container">
                <h2><i class="ri-file-list-3-line"></i> My Waste Reports</h2>

                <div class="filter-bar">
                    <input type="text" id="search" placeholder="Search by location...">
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="empty">Empty</option>
                        <option value="half-full">Half Full</option>
                        <option value="full">Full</option>
                        <option value="overflowing">Overflowing</option>
                    </select>
                </div>

                <table id="reportTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $user_id = $_SESSION['user']['id'];
                        $stmt = $pdo->prepare('SELECT * FROM waste_reports WHERE user_id = ? ORDER BY created_at DESC');
                        $stmt->execute([$user_id]);
                        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($reports):
                            foreach ($reports as $i => $r):
                                $status = strtolower($r['status']);
                        ?>
                                <tr>
                                    <td><?php echo ($i + 1); ?></td>
                                    <td><span class="status-pill <?php echo $status; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars($r['location']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="4">No reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const search = document.getElementById('search');
        const filter = document.getElementById('statusFilter');
        const rows = document.querySelectorAll('#reportTable tbody tr');

        function filterReports() {
            const term = search.value.toLowerCase();
            const status = filter.value;

            rows.forEach(row => {
                const loc = row.cells[2].innerText.toLowerCase();
                const stat = row.cells[1].innerText.toLowerCase();
                row.style.display =
                    (loc.includes(term) && (status === "" || stat.includes(status))) ?
                    "" :
                    "none";
            });
        }

        search.addEventListener('input', filterReports);
        filter.addEventListener('change', filterReports);
    </script>
</body>
<?php if (isset($_GET['success'])): ?>
    <div style="background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;">
        ✅ Report submitted successfully!
    </div>
<?php endif; ?>


</html>