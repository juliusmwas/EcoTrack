<?php
session_start();
require_once '../config.php'; // adjust path if config.php is in a different folder

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

    // Insert the report into DB
    $stmt = $pdo->prepare("
        INSERT INTO waste_reports (user_id, location, status, image, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $location, $status, $imagePath]);

    // Redirect with success message
    header("Location: my_reports.php?success=1");
    exit;
} else {
    header("Location: resident.php");
    exit;
}
