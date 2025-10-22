<?php
// index.php - EcoTrack (Smart Waste Management System)
session_start();
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role == 'admin') header('Location: dashboard/admin.php');
    elseif ($role == 'collector') header('Location: dashboard/collector.php');
    else header('Location: dashboard/resident.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTrack | Smart Waste Management System</title>
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
            background: var(--light-bg);
            color: var(--dark-text);
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: var(--primary);
            color: var(--light-text);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            box-shadow: 0 2px 5px var(--shadow);
            z-index: 10;
        }

        nav .logo {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        nav ul {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }

        nav ul li a {
            color: var(--light-text);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        nav ul li a:hover {
            color: var(--accent);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(120deg, var(--primary) 60%, var(--accent));
            color: var(--light-text);
            padding: 0 1rem;
        }

        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
        }

        .hero p {
            max-width: 600px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .hero .cta {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .hero a {
            text-decoration: none;
            padding: 0.9rem 1.8rem;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .hero .btn-primary {
            background: var(--light-text);
            color: var(--primary);
        }

        .hero .btn-primary:hover {
            background: #e9e9e9;
        }

        .hero .btn-secondary {
            border: 2px solid #fff;
            color: #fff;
        }

        .hero .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* About Section */
        .about {
            padding: 6rem 10%;
            background: var(--light-bg);
            text-align: center;
        }

        .about h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .about p {
            max-width: 800px;
            margin: auto;
            font-size: 1rem;
            line-height: 1.8;
            color: #333;
        }

        /* Features Section */
        .features {
            padding: 6rem 10%;
            background: #fff;
            text-align: center;
        }

        .features h2 {
            color: var(--primary);
            margin-bottom: 3rem;
            font-size: 2rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--light-bg);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow);
            transition: 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            color: #555;
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta-section {
            background: var(--primary);
            color: var(--light-text);
            text-align: center;
            padding: 5rem 2rem;
        }

        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .cta-section a {
            display: inline-block;
            margin-top: 1.5rem;
            background: var(--accent);
            color: var(--light-text);
            padding: 0.9rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .cta-section a:hover {
            background: #37a471;
        }

        /* Footer */
        footer {
            background: #0e4e4a;
            color: var(--light-text);
            padding: 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        footer a {
            color: var(--accent);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width:768px) {
            .hero h1 {
                font-size: 2.2rem;
            }

            .about,
            .features {
                padding: 4rem 5%;
            }
        }
    </style>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav>
        <div class="logo">EcoTrack</div>
        <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <section class="hero" id="home">
        <h1>Smart, Connected & Clean Cities</h1>
        <p>EcoTrack enables real-time waste reporting and monitoring, connecting communities, collectors, and city authorities for a cleaner and sustainable urban environment.</p>
        <div class="cta">
            <a href="register.php" class="btn-primary">Get Started</a>
            <a href="#about" class="btn-secondary">Learn More</a>
        </div>
    </section>

    <section class="about" id="about">
        <h2>About EcoTrack</h2>
        <p>EcoTrack is a web-based smart waste management platform designed to enhance urban cleanliness through real-time reporting and tracking. It empowers residents to report full or overflowing waste bins directly from their devices, while providing municipal authorities and waste collectors with an intuitive dashboard to visualize, plan, and manage collection routes efficiently.</p>
    </section>

    <section class="features" id="features">
        <h2>Key Features</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Real-Time Reporting</h3>
                <p>Residents can instantly report the status of waste bins with geolocation data, ensuring rapid response and clean neighborhoods.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>Interactive Dashboard</h3>
                <p>Municipal officers access an intelligent dashboard to monitor bin locations, track reports, and analyze collection patterns.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-users"></i>
                <h3>Community Engagement</h3>
                <p>EcoTrack bridges communities and authorities, fostering shared responsibility and collaboration for a cleaner city.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-recycle"></i>
                <h3>Sustainability Insights</h3>
                <p>Track trends, optimize routes, and contribute to environmental sustainability using data-driven insights.</p>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <h2>Join EcoTrack Today</h2>
        <p>Be part of a cleaner, smarter, and more connected community. Together, we can make urban living sustainable.</p>
        <a href="register.php">Create Account</a>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> EcoTrack. All Rights Reserved.<br>
            Designed & Developed by Julius Mwangi Kiai | <a href="mailto:support@ecotrack.com">Contact</a></p>
    </footer>
</body>

</html>