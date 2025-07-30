<?php

// Include the database connection
include 'db_connect.php';
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php"); // Redirect to login if not logged in or not a customer
    exit();
}

// Fetch menu items from the database
$sql = "SELECT * FROM menu";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Golden Fork Menu</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        :root {
            --card-deep-navy: linear-gradient(135deg, #0f1c33, #1e3255);
            --deep-coral: #d43f31;
            --hover-coral: #ff5c4d;
            --ivory-bg: #f9f6f2;
            --text-dark: #1c2526;
            --fork-teal: #26a69a;
            --storm-cloud: #2e3b4e;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--ivory-bg);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        header {
            background: linear-gradient(rgba(46, 59, 78, 0.9), rgba(46, 59, 78, 0.7)), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 4rem 0;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .fork-icon {
            position: absolute;
            top: 2rem;
            left: 2rem;
            font-size: 3rem;
            color: var(--fork-teal);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-10deg); }
            50% { transform: translateY(-10px) rotate(10deg); }
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 60px;
        }

        .wave .shape-fill {
            fill: var(--ivory-bg);
        }

        .container {
            max-width: 1600px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            font-family: 'Montserrat', sans-serif;
            color: var(--storm-cloud);
            font-size: 2.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .container h2::after {
            content: '';
            width: 100px;
            height: 3px;
            background: var(--deep-coral);
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .menu-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: var(--card-deep-navy);
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            max-height: 350px;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(212, 63, 49, 0.4);
        }

        .menu-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .menu-card:hover .menu-image {
            transform: scale(1.08);
        }

        .card-body {
            padding: 1.25rem;
            background: rgba(255, 255, 255, 0.05);
        }

        .card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            text-transform: capitalize;
            color: var(--ivory-bg);
        }

        .card-text {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: #e0e0e0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--deep-coral);
        }

        .order-btn {
            background: var(--deep-coral);
            border: none;
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: white;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        .order-btn:hover {
            background: var(--hover-coral);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 92, 77, 0.5);
        }

        .back-btn {
            display: inline-block;
            margin: 2rem auto;
            background: var(--storm-cloud);
            color: white !important;
            border-radius: 25px;
            padding: 0.7rem 2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: center;
        }

        .back-btn:hover {
            background: var(--deep-coral);
            transform: scale(1.05);
        }

        footer {
            background: var(--storm-cloud);
            color: var(--ivory-bg);
            text-align: center;
            padding: 1.5rem;
            font-size: 0.9rem;
            letter-spacing: 1px;
            position: sticky;
            bottom: 0;
            border-top: 3px solid var(--deep-coral);
        }

        .no-items {
            font-size: 1.2rem;
            color: var(--text-dark);
            text-align: center;
            padding: 2rem;
            background: var(--ivory-bg);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2.2rem;
            }
            .container h2 {
                font-size: 2rem;
            }
            .menu-image {
                height: 120px;
            }
            .card-title {
                font-size: 1.2rem;
            }
            .card-body {
                padding: 1rem;
            }
            .card-text {
                font-size: 0.85rem;
            }
            .price {
                font-size: 1.1rem;
            }
            .order-btn {
                padding: 0.4rem 1.2rem;
                font-size: 0.8rem;
            }
            .fork-icon {
                font-size: 2rem;
                top: 1rem;
                left: 1rem;
            }
        }

        @media (max-width: 576px) {
            header {
                padding: 2rem 1rem;
            }
            .container {
                margin: 1.5rem 0.5rem;
                padding: 1rem;
            }
            header h1 {
                font-size: 1.8rem;
            }
            .menu-image {
                height: 100px;
            }
            .card-title {
                font-size: 1rem;
            }
            .card-text {
                font-size: 0.8rem;
            }
            .back-btn {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<header>
    <i class="fas fa-utensils fork-icon"></i>
    <h1>Golden Fork Menu</h1>
    <div class="wave">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>
</header>

<div class="container">
    <h2>Storm into Flavor</h2>

    <div class="row g-3">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card menu-card">
                        <?php if (!empty($row['image']) && file_exists("Uploads/" . $row['image'])): ?>
                            <img src="Uploads/<?php echo htmlspecialchars($row['image']); ?>" class="menu-image" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x150?text=No+Image" class="menu-image" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <span class="price">TK. <?php echo number_format($row['price'], 2); ?></span>
                                <a href="order.php?item_id=<?php echo $row['item_id']; ?>" class="btn order-btn">Order</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="no-items">No items found in the menu.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center">
        <a href="customer_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</div>

<footer>
    <p>© 2025 Golden Fork | Ride the Culinary Wave</p>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>