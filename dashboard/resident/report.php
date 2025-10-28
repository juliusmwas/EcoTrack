<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'resident') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report | EcoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.7.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --primary: #1B7F79;
            --accent: #42B883;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        .report-container {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px var(--shadow);
            max-width: 700px;
            margin: 2rem auto;
        }

        .report-container h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        form label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 600;
            color: #333;
        }

        form input,
        form select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: 0.3s;
            font-size: 1rem;
        }

        form input:focus,
        form select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 5px rgba(66, 184, 131, 0.3);
        }

        form button {
            background: var(--accent);
            color: #fff;
            padding: 0.9rem 1.8rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            font-size: 1rem;
        }

        form button:hover {
            background: #37a471;
            transform: translateY(-2px);
        }

        /* ✅ Responsive Styling */
        @media (max-width: 768px) {
            .report-container {
                margin: 1rem;
                padding: 1.2rem;
                box-shadow: 0 2px 8px var(--shadow);
            }

            .report-container h2 {
                font-size: 1.4rem;
            }

            form input,
            form select {
                padding: 0.7rem;
                font-size: 0.95rem;
            }

            form button {
                padding: 0.8rem;
                font-size: 0.95rem;
            }

            p {
                font-size: 0.95rem;
                line-height: 1.4;
            }
        }

        @media (max-width: 480px) {
            .report-container {
                margin: 0.8rem;
                padding: 1rem;
                border-radius: 10px;
            }

            .report-container h2 {
                font-size: 1.2rem;
            }

            form input,
            form select {
                font-size: 0.9rem;
                padding: 0.6rem;
            }

            form button {
                font-size: 0.9rem;
                padding: 0.7rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../sidebar.php'; ?>
    <div style="flex:1; display:flex; flex-direction:column;">
        <?php include '../navbar.php'; ?>

        <div class="main-content">
            <div class="report-container">
                <h2><i class="ri-map-pin-line"></i> Submit a New Waste Bin Report</h2>
                <p style="color:#555;">Fill out the form below to report the current status of a waste bin near you.</p>

                <form action="submit_report.php" method="POST" enctype="multipart/form-data">
                    <label for="status">Bin Status:</label>
                    <select name="status" id="status" required>
                        <option value="">-- Select Bin Status --</option>
                        <option value="empty">Empty</option>
                        <option value="half-full">Half Full</option>
                        <option value="full">Full</option>
                        <option value="overflowing">Overflowing</option>
                    </select>

                    <label for="location">Location:</label>
                    <input type="text" name="location" id="location" placeholder="Enter or click to auto-fill" required>

                    <label for="image">Upload Image (optional):</label>
                    <input type="file" name="image" id="image" accept="image/*">

                    <button type="submit"><i class="ri-send-plane-fill"></i> Submit Report</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Optional: auto-fill location
        document.addEventListener('DOMContentLoaded', () => {
            const locationInput = document.getElementById('location');
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    const {
                        latitude,
                        longitude
                    } = pos.coords;
                    locationInput.value = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
                });
            }
        });
    </script>
</body>

</html>