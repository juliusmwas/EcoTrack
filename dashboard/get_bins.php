<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, name, latitude AS lat, longitude AS lng, status FROM waste_bins");
    $bins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($bins);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
