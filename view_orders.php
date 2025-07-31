<?php
// Include the database connection
include 'db_connect.php';

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Update order status if request is made
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $updateQuery = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Delete order if request is made
if (isset($_GET['delete'])) {
    $order_id = $_GET['delete'];
    $conn->query("DELETE FROM order_details WHERE order_id = $order_id");
    $conn->query("DELETE FROM orders WHERE order_id = $order_id");
    header("Location: view_orders.php");
    exit();
}

$query = "
    SELECT 
        orders.order_id, 
        users.user_name AS user_name, 
        orders.total_amount, 
        orders.order_date, 
        orders.status, 
        GROUP_CONCAT(CONCAT(menu.name, ' (x', order_details.quantity, ')') SEPARATOR ', ') AS items
    FROM 
        orders
    JOIN 
        users ON orders.user_id = users.user_id
    JOIN 
        order_details ON orders.order_id = order_details.order_id
    JOIN 
        menu ON order_details.item_id = menu.item_id
    GROUP BY 
        orders.order_id
    ORDER BY 
        orders.order_date DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - Golden Fork</title>
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

        .table-container {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
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

        .table tbody tr {
            transition: background 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(0, 168, 150, 0.05);
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
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

        .form-select {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
        }

        .btn-update {
            background: var(--gf-accent-teal);
            border: none;
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 8px;
            color: var(--gf-text-light);
        }

        .btn-update:hover {
            background: #008774;
        }

        .btn-delete {
            background: var(--gf-accent-burgundy);
            border: none;
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 8px;
            color: var(--gf-text-light);
        }

        .btn-delete:hover {
            background: #7a0b34;
        }

        .btn-back {
            background: var(--gf-primary-dark);
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px;
            color: var(--gf-text-light);
            margin-top: 20px;
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
            .table th, .table td {
                padding: 10px;
                font-size: 0.85rem;
            }
            .form-select, .btn-update, .btn-delete {
                font-size: 0.8rem;
                padding: 6px 10px;
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
                <li><a href="manage_menu.php"><i class="fas fa-utensils"></i> Manage Menu</a></li>
                <li><a href="view_orders.php"><i class="fas fa-box-open"></i> View Orders</a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="user_management.php"><i class="fas fa-users-cog"></i> User Management</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="toggle-btn d-none"><i class="fas fa-bars"></i></button>
            <div class="welcome-message">
                <h2>Order Management</h2>
                <p>View and manage customer orders efficiently.</p>
            </div>

            <div class="table-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Update Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $row['order_id'] ?></td>
                                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                                        <td>$<?= number_format($row['total_amount'], 2) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($row['order_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['items']) ?></td>
                                        <td>
                                            <span class="status-<?= strtolower($row['status']) ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                                <select name="status" class="form-select me-2">
                                                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="Cancelled" <?= $row['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" class="btn btn-update">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="?delete=<?= $row['order_id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this order?')">Delete</a>
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

            <a href="admin_dashboard.php" class="btn btn-back">Back to Dashboard</a>
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