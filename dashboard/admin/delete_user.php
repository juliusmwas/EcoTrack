<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized.";
    exit;
}

require_once "../../config.php";

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if (!$user_id) {
    http_response_code(400);
    echo "Missing user id.";
    exit;
}

// Prevent deleting yourself
if ($user_id === (int)$_SESSION['user']['id']) {
    echo "You cannot delete your own account.";
    exit;
}

// Ensure user exists and is not an admin
$stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo "User not found.";
    exit;
}

if ($user['role'] === 'admin') {
    echo "Cannot delete an admin account.";
    exit;
}

// OPTIONAL: remove user-related data first (waste_reports, activity_logs, etc.).
// Uncomment or adapt as needed:
// $pdo->prepare("DELETE FROM waste_reports WHERE user_id = ?")->execute([$user_id]);
// $pdo->prepare("DELETE FROM activity_logs WHERE user_id = ?")->execute([$user_id]);

$del = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($del->execute([$user_id])) {
    echo "User deleted successfully.";
} else {
    http_response_code(500);
    echo "Failed to delete user.";
}
