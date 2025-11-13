<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'collector') {
    header("Location: ../login.php");
    exit;
}

require_once '../../config.php';
require_once 'logactivity.php';

$collector_id = $_SESSION['user']['id'];

// ✅ Handle profile update
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?,  password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hashed_password, $collector_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email,  $collector_id]);
        }

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        logActivity($collector_id, "Updated profile information");
        $success = "Profile updated successfully!";
    } catch (Exception $e) {
        $error = "Failed to update profile. Please try again.";
    }
}

// ✅ Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$collector_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

function safe($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | EcoTrack</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --success: #5CB85C;
            --warning: #F0AD4E;
            --info: #5BC0DE;
            --shadow: rgba(0, 0, 0, 0.15);
            --bg: #F5F8F7;
        }

        body {
            background: var(--bg);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .main-content {
            margin-left: 230px;
            padding: 2rem;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow);
            padding: 2rem;
            max-width: 700px;
            margin: auto;
        }

        .profile-card h3 {
            margin-top: 0;
            color: var(--primary);
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .profile-form label {
            font-weight: 600;
            color: #444;
        }

        .profile-form input {
            padding: 0.6rem 0.8rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
        }

        .profile-form button {
            margin-top: 1rem;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 0.7rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .profile-form button:hover {
            background: #369f85;
        }

        .notification {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 3px 8px var(--shadow);
            font-weight: 600;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <h2><i class="ri-user-line"></i> My Profile</h2>

            <div class="profile-card">
                <h3>Profile Information</h3>

                <?php if ($success): ?>
                    <div class="notification success"><?php echo safe($success); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="notification error"><?php echo safe($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="profile-form">
                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" value="<?php echo safe($user['name']); ?>" required>
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" value="<?php echo safe($user['email']); ?>" required>
                    </div>

                    <div>
                        <label for="password">New Password <small>(leave blank to keep current)</small></label>
                        <input type="password" name="password" id="password" placeholder="Enter new password">
                    </div>
                    <button type="submit"><i class="ri-save-line"></i> Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>