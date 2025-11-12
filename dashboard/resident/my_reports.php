<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
    header("Location: ../login.php");
    exit;
}
require_once '../../config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports | EcoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
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
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            gap: 0.8rem;
        }

        .filter-bar input,
        .filter-bar select {
            padding: 0.7rem;
            border: 1px solid #ccc;
            border-radius: 10px;
            flex: 1;
            min-width: 220px;
        }

        /* --- Cards Grid --- */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .report-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.3rem;
            box-shadow: 0 4px 12px var(--shadow);
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }

        .status-pill {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #fff;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pill.empty {
            background: #5cb85c;
        }

        .status-pill["half-full"] {
            background: #5bc0de;
        }

        .status-pill.full {
            background: #f0ad4e;
        }

        .status-pill.overflowing {
            background: #d9534f;
        }

        .card-body {
            margin-top: 1.2rem;
        }

        .card-body h3 {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .card-body p {
            font-size: 0.9rem;
            color: #555;
            margin: 0.3rem 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .no-reports {
            text-align: center;
            color: #888;
            font-size: 1rem;
            margin-top: 2rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .report-card {
            animation: fadeInUp 0.4s ease;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-recycle-line"></i> My Waste Reports</h2>

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

            <div class="reports-grid" id="reportsGrid">
                <?php
                $user_id = $_SESSION['user']['id'];
                $stmt = $pdo->prepare('SELECT * FROM waste_reports WHERE user_id = ? ORDER BY created_at DESC');
                $stmt->execute([$user_id]);
                $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($reports):
                    foreach ($reports as $r):
                        $status = strtolower($r['status']);
                ?>
                        <div class="report-card" data-location="<?php echo htmlspecialchars($r['location']); ?>" data-status="<?php echo $status; ?>">
                            <span class="status-pill <?php echo $status; ?>"><?php echo ucfirst($r['status']); ?></span>
                            <div class="card-body">
                                <h3><i class="ri-map-pin-line"></i> <?php echo htmlspecialchars($r['location']); ?></h3>
                                <p><i class="ri-calendar-line"></i> <?php echo date('Y-m-d', strtotime($r['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p class="no-reports">No waste reports found yet. Submit one to get started!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const search = document.getElementById('search');
        const filter = document.getElementById('statusFilter');
        const cards = document.querySelectorAll('.report-card');

        function filterReports() {
            const term = search.value.toLowerCase();
            const status = filter.value;

            cards.forEach(card => {
                const loc = card.getAttribute('data-location').toLowerCase();
                const stat = card.getAttribute('data-status');
                card.style.display =
                    (loc.includes(term) && (status === "" || stat.includes(status))) ?
                    "" :
                    "none";
            });
        }

        search.addEventListener('input', filterReports);
        filter.addEventListener('change', filterReports);
    </script>
</body>

</html>