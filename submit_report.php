<?php
// submit_report.php - Handles waste report submission
session_start();
require_once "../config.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user']['id'];
    $location = trim($_POST['location']);
    $imagePath = null;

    // Handle optional image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = "uploads/" . $filename;
        }
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO waste_reports (user_id, location, image) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $location, $imagePath])) {
        echo "<script>alert('✅ Report submitted successfully!'); window.location.href='resident.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to submit report. Please try again.'); window.history.back();</script>";
    }
}
