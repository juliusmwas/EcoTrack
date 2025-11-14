<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

require_once "../../config.php";

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

if (!$user_id || $new_status === '') {
    http_response_code(400);
    echo "Missing data.";
    exit;
}

// Prevent admin from changing their own status accidentally
if ($user_id === (int)$_SESSION['user']['id']) {
    echo "You cannot change your own account status.";
    exit;
}

// Make sure user exists and isn't an admin
$stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo "User not found.";
    exit;
}

if ($user['role'] === 'admin') {
    echo "Cannot change status of an admin account.";
    exit;
}

// Validate new_status value (adjust if your DB uses different values)
$allowed = ['active', 'blocked'];
if (!in_array($new_status, $allowed)) {
    http_response_code(400);
    echo "Invalid status.";
    exit;
}

$update = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
if ($update->execute([$new_status, $user_id])) {
    echo "User status updated to " . htmlspecialchars($new_status);
} else {
    http_response_code(500);
    echo "Failed to update user status.";
}
