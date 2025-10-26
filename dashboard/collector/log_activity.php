<?php
require_once __DIR__ . '/../../config.php';

function logActivity($user_id, $action)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
