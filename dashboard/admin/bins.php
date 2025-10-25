<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once '../config.php';
$bins = $pdo->query("SELECT * FROM bins WHERE status='active' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Bins - Collector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h3 class="text-center mb-4">🗑️ Available Waste Bins</h3>
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Added On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bins as $bin): ?>
                        <tr>
                            <td><?= $bin['id'] ?></td>
                            <td><?= htmlspecialchars($bin['location']) ?></td>
                            <td><?= ucfirst($bin['status']) ?></td>
                            <td><?= $bin['created_at'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bins)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No bins available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>