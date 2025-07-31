<?php

// Include the database connection
include 'db_connect.php';

ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Golden Fork</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700;900&display=stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&display=stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --gf-primary-dark: #1A2B40;
            --gf-secondary-dark: #263859;
            --gf-accent-gold: #FFC300;
            --gf-accent-teal: #00A896;
            --gf-accent-burgundy: #900C3F;
            --gf-light: #F8F9FA;
            --gf-text-dark: #222;
            --gf-text-light: #fff;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--gf-primary-dark);
            color: var(--gf-text-light);
            padding: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar .logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 2rem;
            color: var(--gf-accent-gold);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar .logo-text i {
            color: var(--gf-accent-teal);
            margin-left: 10px;
            font-size: 1.5rem;
            animation: floatSpoon 3s ease-in-out infinite;
        }

        @keyframes floatSpoon {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(3deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
        }

        .sidebar-nav li {
            margin-bottom: 10px;
        }

        .sidebar-nav a {
            color: var(--gf-text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .sidebar-nav a:hover {
            background: var(--gf-secondary-dark);
        }

        .sidebar-nav a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            padding: 30px;
        }

        .welcome-message {
            background: var(--gf-light);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-message h2 {
            font-family: 'Merriweather', serif;
            font-size: 2rem;
            color: var(--gf-primary-dark);
            margin-bottom: 10px;
        }

        .welcome-message p {
            font-size: 1rem;
            color: #555;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: linear-gradient(145deg, var(--gf-accent-teal), #008774);
            color: var(--gf-text-light);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--gf-accent-gold);
            transition: transform 0.3s ease;
        }

        .card:hover i {
            transform: scale(1.1);
        }

        .card h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 0.9rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .card a {
            color: var(--gf-text-light);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            background: var(--gf-accent-burgundy);
            padding: 8px 15px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .card a:hover {
            background: #7a0b34;
        }

        .btn-back {
            background: var(--gf-primary-dark);
            border: none;
            padding: 10px 20px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 1rem;
            margin-top: 30px;
            display: inline-block;
        }

        .btn-back:hover {
            background: var(--gf-secondary-dark);
        }

        footer {
            text-align: center;
            padding: 15px;
            background: var(--gf-primary-dark);
            color: var(--gf-text-light);
            margin-top: 30px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .toggle-btn {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1100;
                font-size: 1.5rem;
                color: var(--gf-primary-dark);
                background: var(--gf-light);
                padding: 10px;
                border-radius: 8px;
            }
        }

        @media (max-width: 576px) {
            .welcome-message h2 {
                font-size: 1.5rem;
            }
            .card {
                padding: 20px;
            }
            .card h3 {
                font-size: 1.3rem;
            }
            .card i {
                font-size: 2rem;
            }
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-section">
                <div class="logo-text">Golden Fork <i class="fas fa-utensils"></i></div>
            </div>
            <ul class="sidebar-nav">
                <li><a href="order.php"><i class="fas fa-utensils"></i> Order Food</a></li>
                <li><a href="my_orders.php"><i class="fas fa-history"></i> My Orders</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="toggle-btn d-none"><i class="fas fa-bars"></i></button>
            <div class="welcome-message">
                <h2>Welcome to Your Dashboard!</h2>
                <p>Explore our menu, place orders, and track your dining journey.</p>
            </div>

            <div class="dashboard">
                <div class="card">
                    <i class="fas fa-book-open"></i>
                    <h3>View Menu</h3>
                    <p>Discover a variety of delicious dishes.</p>
                    <a href="menu.php">View Menu</a>
                </div>
                <div class="card">
                    <i class="fas fa-utensils"></i>
                    <h3>Order Food</h3>
                    <p>Order your favorite meals now.</p>
                    <a href="order.php">Order Now</a>
                </div>
                <div class="card">
                    <i class="fas fa-history"></i>
                    <h3>My Orders</h3>
                    <p>Track your past orders and their status.</p>
                    <a href="my_orders.php">View Orders</a>
                </div>
            </div>

            <a href="logout.php" class="btn-back">Logout</a>
        </div>
    </div>

    <footer>
        <p>© 2025 Golden Fork Restaurant. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.sidebar');
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        });
    </script>
</body>
</html>