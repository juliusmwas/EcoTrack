<?php
require_once "../config.php";

/**
 * Log an activity into the activity_logs table.
 *
 * @param int $user_id The ID of the user performing the action
 * @param string $action A short description of what happened
 */
function logActivity($user_id, $action)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        // Optional: write errors to a file or ignore silently in production
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
