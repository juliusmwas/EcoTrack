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
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">🗑️ Manage Waste Bins</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Add Bin Form -->
        <div class="card mb-4 shadow-sm">
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
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Existing Bins</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
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
                                    <td><?= ucfirst($bin['status']) ?></td>
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
    </div>
</body>

</html>