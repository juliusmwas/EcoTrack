<?php
session_start();
require_once '../config.php';
require_once './log_activity.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $status = $_POST['status'] ?? 'pending';
    $location = $_POST['location'] ?? '';
    $imagePath = null;

    // ✅ Handle optional image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'uploads/' . $fileName;
        }
    }

    // ✅ Insert report
    $stmt = $pdo->prepare("
        INSERT INTO waste_reports (user_id, location, status, image, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $location, $status, $imagePath]);

    // ✅ Log the action
    logActivity($user_id, "Submitted a new waste report at location: {$location}");

    // ✅ Redirect with success message
    header("Location: my_reports.php?success=1");
    exit;
} else {
    header("Location: resident.php");
    exit;
}
