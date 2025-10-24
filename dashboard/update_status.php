<?php
require_once "../config.php";

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];

    $query = "UPDATE waste_reports SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['status' => $status, 'id' => $id]);

    echo "✅ Status updated successfully!";
} else {
    echo "❌ Invalid request.";
}
