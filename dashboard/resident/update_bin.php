<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';

    $valid = ['empty', 'half-full', 'full', 'overflowing'];
    if (!in_array($status, $valid)) {
        echo json_encode(["error" => "Invalid status"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE waste_bins SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    echo json_encode(["success" => true]);
}
