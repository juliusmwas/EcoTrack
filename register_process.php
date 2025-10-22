<?php
// register_process.php - Handles new user registration
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = $_POST["role"];

    // Validate password match
    if ($password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match.'); window.history.back();</script>";
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "<script>alert('⚠️ Email already registered. Please use another.'); window.history.back();</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into DB
    $insert = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
    if ($insert->execute([$fullname, $email, $hashedPassword, $role])) {
        echo "<script>alert('✅ Registration successful! You can now log in.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('❌ Registration failed. Please try again later.'); window.history.back();</script>";
    }
}
