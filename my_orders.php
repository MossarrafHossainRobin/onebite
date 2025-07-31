<?php

// Include the database connection
include 'db_connect.php';
  

ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $order_id = $_POST['cancel_order_id'];
    
    // Begin transaction
    $conn->begin_transaction();
    try {
        // Delete from order_details first
        $stmt = $conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        
        // Delete from orders (case-insensitive status check)
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ? AND user_id = ? AND LOWER(status) = 'pending'");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Order not found or not in pending status");
        }
        
        $conn->commit();
        header("Location: my_orders.php?message=Order cancelled successfully");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: my_orders.php?error=Failed to cancel order: " . $e->getMessage());
    }
    exit();
}

$query = "
    SELECT orders.order_id, orders.total_amount, orders.order_date, orders.status,
           order_details.item_id, order_details.quantity, order_details.price, 
           menu.name AS item_name
    FROM orders
    JOIN order_details ON orders.order_id = order_details.order_id
    JOIN menu ON order_details.item_id = menu.item_id
    WHERE orders.user_id = ?
    ORDER BY orders.order_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Golden Fork</title>
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

        .order-history {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-history h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
            color: var(--gf-primary-dark);
            margin-bottom: 20px;
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

        .status-confirmed {
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

        .countdown {
            font-weight: 500;
            color: #e74c3c;
        }

        .countdown.expired {
            color: #999;
        }

        .countdown.warning {
            color: #e74c3c;
            font-weight: 600;
        }

        .btn-cancel {
            background: var(--gf-accent-burgundy);
            border: none;
            padding: 8px 15px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-cancel:disabled {
            background: #ccc;
            cursor: not-allowed;
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
            text-decoration: none;
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
            .order-history h3 {
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
                <li><a href="my_orders.php"><i class="fas fa-history"></i> My Orders</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="payment.php"><i class="fas fa-credit-card"></i> Payments</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="toggle-btn d-none"><i class="fas fa-bars"></i></button>
            <div class="welcome-message">
                <h2>Your Order History</h2>
                <p>View details of your past and current orders below.</p>
            </div>

            <div class="order-history">
                <h3>Order Details</h3>
                <?php
                if ($result->num_rows > 0) {
                    echo "<div class='table-responsive'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Order Time</th>
                                        <th>Status</th>
                                        <th>Time Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>";
                    while ($row = $result->fetch_assoc()) {
                        $order_id = $row['order_id'];
                        $order_time = strtotime($row['order_date']);
                        $end_time = $order_time + 60; // 30 seconds for cancellation
                        $status = strtolower(trim($row['status']));
                        $status_class = $status === 'pending' ? 'pending' : $status;
                        $is_pending = (time() - $order_time) < 60; // Show cancel button for 30 seconds
                        
                        echo "<tr>
                                <td>#{$row['order_id']}</td>
                                <td>" . htmlspecialchars($row['item_name']) . "</td>
                                <td>{$row['quantity']}</td>
                                <td>TK. " . number_format($row['price'], 2) . "</td>
                                <td>TK. " . number_format($row['total_amount'], 2) . "</td>
                                <td>" . date('M d, Y H:i', $order_time) . "</td>
                                <td><span class='status-{$status_class}'>" . htmlspecialchars($row['status']) . "</span></td>
                                <td class='countdown' data-endtime='{$end_time}' data-orderid='{$order_id}'></td>
                                <td>";
                        if ($is_pending) {
                            echo "<form method='POST' onsubmit='return confirm(\"Are you sure you want to cancel this order?\");'>
                                    <input type='hidden' name='cancel_order_id' value='{$order_id}'>
                                    <button type='submit' class='btn-cancel'>Cancel Order</button>
                                  </form>";
                        } else {
                            echo "No action available";
                        }
                        echo "</td></tr>";
                    }
                    echo "</tbody></table></div>";
                } else {
                    echo "<p class='text-center text-muted'>You have not placed any orders yet.</p>";
                }

                $stmt->close();
                $conn->close();
                ?>
            </div>

            <a href="customer_dashboard.php" class="btn-back">Back to Dashboard</a>
        </div>
    </div>

    <footer>
        <p>© 2025 Golden Fork Restaurant. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer
        function updateCountdowns() {
            const now = Math.floor(Date.now() / 1000);
            document.querySelectorAll('.countdown').forEach(function(cell) {
                const endTime = parseInt(cell.getAttribute('data-endtime'));
                let diff = endTime - now;

                if (diff <= 0) {
                    cell.innerText = 'Confirmed';
                    cell.classList.add('expired');
                    cell.classList.remove('warning');
                    
                    // Disable cancel button
                    const cancelBtn = cell.parentElement.querySelector('.btn-cancel');
                    if (cancelBtn) {
                        cancelBtn.disabled = true;
                    }
                } else {
                    cell.innerText = `${diff}s`;
                    cell.classList.add('warning');
                }
            });
        }

        setInterval(updateCountdowns, 1000);
        window.onload = updateCountdowns;

        // Sidebar toggle
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