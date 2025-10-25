<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../config.php';

// ✅ Add bin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['location'])) {
    $location = trim($_POST['location']);
    if ($location !== '') {
        $stmt = $pdo->prepare("INSERT INTO bins (location) VALUES (?)");
        $stmt->execute([$location]);
        $success = "✅ Bin added successfully!";
    }
}

// ✅ Delete bin
if (isset($_GET['delete'])) {
    $bin_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM bins WHERE id = ?");
    $stmt->execute([$bin_id]);
    $success = "🗑️ Bin deleted successfully!";
}

// ✅ Fetch all bins
$bins = $pdo->query("SELECT * FROM bins ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bins - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --sidebar-width: 230px;
            --primary: #1B7F79;
            --light-bg: #F5F8F7;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Layout structure */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
        }

        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 100px 25px 40px 25px;
            /* top padding leaves space for fixed navbar */
            transition: all 0.3s ease;
        }

        /* Navbar */
        .navbar-fixed {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 6px var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 90px 15px 30px 15px;
            }

            .navbar-fixed {
                left: 0;
            }
        }

        /* Card and Table Styling */
        .card {
            border-radius: 12px;
            box-shadow: 0 3px 10px var(--shadow);
        }

        h2 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        table {
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: var(--primary);
            color: white;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background: #13665f;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <?php include '../sidebar.php'; ?>
        </div>

        <!-- Navbar -->
        <div class="navbar-fixed">
            <?php include '../navbar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h2 class="text-center mb-4">🗑️ Manage Waste Bins</h2>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Add Bin Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-9 col-sm-8">
                            <input type="text" name="location" class="form-control" placeholder="Enter bin location" required>
                        </div>
                        <div class="col-md-3 col-sm-4 d-grid">
                            <button type="submit" class="btn btn-primary">➕ Add Bin</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bin List -->
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Existing Bins</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Added On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bins as $bin): ?>
                                    <tr>
                                        <td><?= $bin['id'] ?></td>
                                        <td><?= htmlspecialchars($bin['location']) ?></td>
                                        <td><?= ucfirst($bin['status'] ?? 'active') ?></td>
                                        <td><?= $bin['created_at'] ?></td>
                                        <td>
                                            <a href="?delete=<?= $bin['id'] ?>"
                                                onclick="return confirm('Are you sure you want to delete this bin?')"
                                                class="btn btn-danger btn-sm">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($bins)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No bins found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- End main content -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>