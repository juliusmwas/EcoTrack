<?php


$host = "localhost";       // Database host 
$dbname = "ecotrack_db";   // Database name
$username = "root";        // MySQL username
$password = "";            // mySQL password

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO error mode to exception for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("❌ Database connection failed: " . $e->getMessage());
}
