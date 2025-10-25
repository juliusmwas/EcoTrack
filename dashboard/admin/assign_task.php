<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    die("Unauthorized access.");
}

require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $collector_id = $_POST['collector_id'] ?? null;
    $report_id = $_POST['report_id'] ?? null;

    if (!$collector_id || !$report_id) {
        die("❌ Missing data.");
    }

    try {
        // Check if report exists and is unassigned
        $check = $pdo->prepare("SELECT id FROM waste_reports WHERE id = ? AND (collector_id IS NULL OR status = 'pending')");
        $check->execute([$report_id]);

        if ($check->rowCount() === 0) {
            die("⚠️ This report is already assigned or invalid.");
        }

        //  Assign collector and update status
        $stmt = $pdo->prepare("UPDATE waste_reports SET collector_id = ?, status = 'assigned' WHERE id = ?");
        $stmt->execute([$collector_id, $report_id]);

        echo "✅ Task successfully assigned!";
    } catch (PDOException $e) {
        echo "❌ Database error: " . $e->getMessage();
    }
} else {
    echo "❌ Invalid request.";
}
