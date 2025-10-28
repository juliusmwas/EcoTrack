<?php
require_once "../../config.php";
header('Content-Type: application/json');

$query = "SELECT id, location, latitude, longitude, status, created_at FROM bins WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
$stmt = $pdo->query($query);
$bins = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($bins);
