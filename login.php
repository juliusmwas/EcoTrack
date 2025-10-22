<?php
// login.php - EcoTrack Smart Waste Management System
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrack | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --light-bg: #F5F8F7;
            --dark-text: #1A1A1A;
            --light-text: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(120deg, var(--primary) 60%, var(--accent));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-text);
        }

        .login-container {
            background: var(--light-text);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px var(--shadow);
            width: 100%;
            max-width: 380px;
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .login-container h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        input {
            padding: 0.8rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: var(--accent);
        }

        button {
            background: var(--primary);
            color: var(--light-text);
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #176c68;
        }

        .extra-links {
            margin-top: 1.2rem;
            font-size: 0.9rem;
            color: #333;
        }

        .extra-links a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        footer {
            position: absolute;
            bottom: 1rem;
            text-align: center;
            width: 100%;
            color: var(--light-text);
            font-size: 0.85rem;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1.5rem;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="logo">EcoTrack</div>
        <h2>Welcome Back</h2>
        <form action="authenticate.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="extra-links">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> EcoTrack. All Rights Reserved.</p>
    </footer>

</body>

</html>