<?php

// Include the database connection
include 'db_connect.php';
   
// Start session
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

// Fetch user status from the users table
$user_email = $_SESSION['email'];
$user_status_query = "SELECT status FROM users WHERE email = ?";
$stmt = $conn->prepare($user_status_query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$stmt->bind_result($user_status);
$stmt->fetch();
$stmt->close();

// Initialize order details array if not already set
if (!isset($_SESSION['order_items'])) {
    $_SESSION['order_items'] = [];
}

$error_message = '';

// Check if an item is being added to the order
if (isset($_POST['add_to_order']) && $user_status === 'active') {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    // Fetch item details from the menu
    $sql = "SELECT * FROM menu WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();

    // Add item to the order
    if ($item) {
        $_SESSION['order_items'][] = [
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'total' => $item['price'] * $quantity
        ];
    }
} elseif (isset($_POST['add_to_order']) && $user_status !== 'active') {
    $error_message = "Your account has been disabled. You cannot place any orders.";
}

// Handle order submission
if (isset($_POST['place_order']) && $user_status === 'active') {
    $user_id = $_SESSION['user_id'];
    $total_amount = array_sum(array_column($_SESSION['order_items'], 'total'));
    $order_date = date('Y-m-d H:i:s');

    // Insert order into the orders table
    $sql = "INSERT INTO orders (user_id, total_amount, order_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $user_id, $total_amount, $order_date);
    if ($stmt->execute()) {
        // Get the last inserted order_id
        $order_id = $conn->insert_id;

        // Insert order items into the order_details table
        foreach ($_SESSION['order_items'] as $order_item) {
            $item_id = $order_item['item_id'];
            $quantity = $order_item['quantity'];
            $total = $order_item['total'];

            $sql = "INSERT INTO order_details (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $detail_stmt = $conn->prepare($sql);
            $detail_stmt->bind_param("iiid", $order_id, $item_id, $quantity, $total);
            $detail_stmt->execute();
            $detail_stmt->close();
        }

        // Clear order items after placing the order
        $_SESSION['order_items'] = [];

        echo "<script>alert('Your order has been placed successfully!');</script>";
    } else {
        echo "<script>alert('Error placing the order. Please try again.');</script>";
    }
    $stmt->close();
} elseif (isset($_POST['place_order']) && $user_status !== 'active') {
    $error_message = "Your account has been disabled. You cannot place any orders.";
}

// Handle removing an item from the order
if (isset($_POST['remove_item']) && $user_status === 'active') {
    $item_id = $_POST['item_id'];

    // Remove the item from the order
    foreach ($_SESSION['order_items'] as $key => $order_item) {
        if ($order_item['item_id'] == $item_id) {
            unset($_SESSION['order_items'][$key]);
            break;
        }
    }

    // Reindex the array
    $_SESSION['order_items'] = array_values($_SESSION['order_items']);
} elseif (isset($_POST['remove_item']) && $user_status !== 'active') {
    $error_message = "Your account has been disabled. You cannot place any orders.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food - Golden Fork</title>
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

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-danger .btn-close {
            font-size: 0.8rem;
        }

        .menu-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .menu-item {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .menu-item:hover {
            transform: translateY(-5px);
        }

        .menu-item h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.5rem;
            color: var(--gf-primary-dark);
            margin-bottom: 10px;
        }

        .menu-item p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .menu-item .price {
            font-size: 1.1rem;
            color: var(--gf-accent-teal);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .menu-item form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-item input[type="number"] {
            width: 80px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .menu-item button {
            background: var(--gf-accent-burgundy);
            border: none;
            padding: 8px 15px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .menu-item button:hover {
            background: #7a0b34;
        }

        .order-summary {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }

        .order-summary h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
            color: var(--gf-primary-dark);
            margin-bottom: 20px;
        }

        .order-summary table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .order-summary thead {
            background: var(--gf-primary-dark);
            color: var(--gf-text-light);
        }

        .order-summary th {
            padding: 15px;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid var(--gf-secondary-dark);
        }

        .order-summary td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .order-summary tbody tr:hover {
            background: rgba(0, 168, 150, 0.05);
        }

        .order-summary .total {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--gf-accent-teal);
        }

        .order-summary .btn-remove {
            background: var(--gf-accent-burgundy);
            border: none;
            padding: 8px 15px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .order-summary .btn-remove:hover {
            background: #7a0b34;
        }

        .order-summary .btn-place-order {
            background: var(--gf-accent-teal);
            border: none;
            padding: 10px 20px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 1rem;
            margin-top: 20px;
            display: inline-block;
        }

        .order-summary .btn-place-order:hover {
            background: #008774;
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
        }

        @media (max-width: 576px) {
            .welcome-message h2 {
                font-size: 1.5rem;
            }
            .menu-container {
                grid-template-columns: 1fr;
            }
            .menu-item h3 {
                font-size: 1.3rem;
            }
            .order-summary th, .order-summary td {
                font-size: 0.85rem;
                padding: 10px;
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
                <li><a href="my_orders.php"><i class="fas fa-history"></i> Order History</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <button class="toggle-btn d-none"><i class="fas fa-bars"></i></button>
            <div class="welcome-message">
                <h2>Place Your Order</h2>
                <p>Browse our delicious menu and create your order below.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="menu-container">
                <?php
                $sql = "SELECT * FROM menu";
                $result = $conn->query($sql);
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                    <div class="menu-item">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="price">TK. <?php echo number_format($row['price'], 2); ?></p>
                        <form method="POST" action="order.php">
                            <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                            <input type="number" name="quantity" min="1" value="1" required>
                            <button type="submit" name="add_to_order" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php
                    endwhile;
                else:
                    echo "<p class='text-center text-muted'>No items available in the menu.</p>";
                endif;
                ?>
            </div>

            <?php if (!empty($_SESSION['order_items'])): ?>
                <div class="order-summary">
                    <h3>Your Order Summary</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($_SESSION['order_items'] as $order_item):
                                $total += $order_item['total'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order_item['name']); ?></td>
                                    <td><?php echo $order_item['quantity']; ?></td>
                                    <td>TK. <?php echo number_format($order_item['price'], 2); ?></td>
                                    <td>TK. <?php echo number_format($order_item['total'], 2); ?></td>
                                    <td>
                                        <form method="POST" action="order.php">
                                            <input type="hidden" name="item_id" value="<?php echo $order_item['item_id']; ?>">
                                            <button type="submit" name="remove_item" class="btn-remove">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="total">Total</td>
                                <td class="total">TK. <?php echo number_format($total, 2); ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <form method="POST" action="order.php">
                        <button type="submit" name="place_order" class="btn-place-order">Place Order</button>
                    </form>
                </div>
            <?php endif; ?>

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

            // Auto-close alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>