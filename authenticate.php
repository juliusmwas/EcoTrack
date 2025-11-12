<?php
// authenticate.php - Handles user login authentication
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Check user existence
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        // Valid credentials — start session
        $_SESSION["user"] = [
            "id" => $user["id"],
            "fullname" => $user["fullname"],
            "role" => $user["role"],
            "email" => $user["email"]
        ];

        // Redirect by role
        switch ($user["role"]) {
            case "admin":
                header("Location: dashboard/admin/admin.php");
                break;
            case "collector":
                header("Location: dashboard/collector/collector.php");
                break;
            default:
                header("Location: dashboard/resident/resident.php");
                break;
        }
        exit;
    } else {
        echo "<script>alert('❌ Invalid email or password.'); window.history.back();</script>";
    }
}
