<?php

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

// Fetch user details
$user_email = $_SESSION['email'];
$user_query = "SELECT user_id, user_name, email, status FROM users WHERE email = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch order history
$order_query = "
    SELECT 
        orders.order_id, 
        orders.total_amount, 
        orders.order_date, 
        orders.status, 
        GROUP_CONCAT(CONCAT(menu.name, ' (x', order_details.quantity, ')') SEPARATOR ', ') AS items
    FROM 
        orders
    JOIN 
        order_details ON orders.order_id = order_details.order_id
    JOIN 
        menu ON order_details.item_id = menu.item_id
    WHERE 
        orders.user_id = ?
    GROUP BY 
        orders.order_id
    ORDER BY 
        orders.order_date DESC
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();
$stmt->close();

// Fetch payment details (assuming payments table exists)
$payment_query = "
    SELECT 
        payment_id, 
        order_id, 
        amount, 
        payment_method, 
        payment_date
    FROM 
        payments
    WHERE 
        user_id = ?
    ORDER BY 
        payment_date DESC
";
$stmt = $conn->prepare($payment_query);
$stmt->bind_param("i", $user['user_id']);
$stmt->execute();
$payment_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Golden Fork</title>
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

        .profile-section, .order-history, .payment-details {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .profile-section h3, .order-history h3, .payment-details h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
            color: var(--gf-primary-dark);
            margin-bottom: 20px;
        }

        .profile-details p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-details p strong {
            color: var(--gf-accent-teal);
        }

        .status-active {
            color: #155724;
            background: #d4edda;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        .status-inactive {
            color: #721c24;
            background: #f8d7da;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead {
            background: var(--gf-primary-dark);
            color: var(--gf-text-light);
        }

        .table th {
            padding: 15px;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid var(--gf-secondary-dark);
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: rgba(0, 168, 150, 0.05);
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 12px;
            display: inline-block;
        }

        .btn-back {
            background: var(--gf-primary-dark);
            border: none;
            padding: 10px 20px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 1rem;
            margin-top: 20px;
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
            .table-responsive {
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            .welcome-message h2 {
                font-size: 1.5rem;
            }
            .profile-section h3, .order-history h3, .payment-details h3 {
                font-size: 1.5rem;
            }
            .table th, .table td {
                padding: 10px;
                font-size: 0.85rem;
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
                <li><a href="payment.php"><i class="fas fa-history"></i> Make Payment</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="toggle-btn d-none"><i class="fas fa-bars"></i></button>
            <div class="welcome-message">
                <h2>My Profile</h2>
                <p>View your account details, order history, and payment information.</p>
            </div>

            <!-- Profile Details -->
            <div class="profile-section">
                <h3>Personal Details</h3>
                <div class="profile-details">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['user_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-<?php echo strtolower($user['status']); ?>">
                            <?php echo htmlspecialchars($user['status']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Order History -->
            <div class="order-history">
                <h3>Order History</h3>
                <?php if ($order_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $order_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                                        <td>TK. <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No orders found.</p>
                <?php endif; ?>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
                <h3>Payment Details</h3>
                <?php if ($payment_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Order ID</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $payment_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $payment['payment_id']; ?></td>
                                        <td>#<?php echo $payment['order_id']; ?></td>
                                        <td>TK. <?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No payment records found.</p>
                <?php endif; ?>
            </div>

            <a href="customer_dashboard.php" class="btn-back">Back to Dashboard</a>
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

<?php
// Close the database connection
$conn->close();
?>