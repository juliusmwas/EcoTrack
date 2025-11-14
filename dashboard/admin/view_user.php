<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once "../../config.php";

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT id, fullname AS name, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User | EcoTrack Admin</title>
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

        .profile-card {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 10px var(--shadow);
        }

        .profile-card h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .profile-card p {
            margin: 0.5rem 0;
            color: #555;
            font-size: 0.95rem;
        }

        .back-btn {
            display: inline-block;
            margin-top: 1rem;
            background: var(--accent);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
        }

        .back-btn:hover {
            background: #2fa572;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <div class="profile-card">
                <h2><i class="ri-user-line"></i> <?= htmlspecialchars($user['name']) ?></h2>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
                <p><strong>Registered:</strong> <?= date("M d, Y", strtotime($user['created_at'])) ?></p>

                <a href="manage_users.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Manage Users</a>
            </div>
        </div>
    </div>
</body>

</html>