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

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Fetch payment history
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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$stmt->close();

// Fetch unpaid orders (orders without a payment)
$unpaid_orders_query = "
    SELECT 
        orders.order_id, 
        orders.total_amount, 
        orders.order_date
    FROM 
        orders
    LEFT JOIN 
        payments ON orders.order_id = payments.order_id
    WHERE 
        orders.user_id = ? 
        AND orders.status != 'Cancelled'
        AND payments.payment_id IS NULL
    ORDER BY 
        orders.order_date DESC
";
$stmt = $conn->prepare($unpaid_orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$unpaid_orders_result = $stmt->get_result();
$stmt->close();

// Handle new payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];
    $amount = $_POST['amount'];

    // Validate input
    if (empty($order_id) || empty($payment_method) || empty($amount)) {
        $error_message = "Please fill in all fields.";
    } else {
        // Insert payment into the payments table
        $payment_date = date('Y-m-d H:i:s');
        $insert_query = "
            INSERT INTO payments (order_id, user_id, amount, payment_method, payment_date)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iidds", $order_id, $user_id, $amount, $payment_method, $payment_date);
        
        if ($stmt->execute()) {
            $success_message = "Payment recorded successfully.";
            // Redirect to refresh page and avoid form resubmission
            header("Location: payment.php");
            exit();
        } else {
            $error_message = "Error recording payment. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Golden Fork</title>
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

        .payment-section, .payment-history {
            background: var(--gf-light);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .payment-section h3, .payment-history h3 {
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
            color: var(--gf-primary-dark);
            margin-bottom: 20px;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: var(--gf-text-dark);
            margin-bottom: 8px;
            display: block;
        }

        .form-control, .form-select {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 0.9rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--gf-accent-teal);
            box-shadow: 0 0 8px rgba(0, 168, 150, 0.2);
            outline: none;
        }

        .btn-submit {
            background: var(--gf-accent-teal);
            border: none;
            padding: 10px 20px;
            color: var(--gf-text-light);
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #008774;
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
            .payment-section h3, .payment-history h3 {
                font-size: 1.5rem;
            }
            .table th, .table td {
                padding: 10px;
                font-size: 0.85rem;
            }
            .form-control, .form-select {
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
                <h2>Manage Your Payments</h2>
                <p>View your payment history and make payments for your orders.</p>
            </div>

            <!-- Alerts -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- New Payment Form -->
            <div class="payment-section">
                <h3>Make a Payment</h3>
                <?php if ($unpaid_orders_result->num_rows > 0): ?>
                    <form method="POST" action="payment.php">
                        <div class="form-group">
                            <label for="order_id" class="form-label">Select Order</label>
                            <select name="order_id" id="order_id" class="form-select" required onchange="updateAmount(this)">
                                <option value="">Choose an order</option>
                                <?php while ($order = $unpaid_orders_result->fetch_assoc()): ?>
                                    <option value="<?php echo $order['order_id']; ?>" data-amount="<?php echo $order['total_amount']; ?>">
                                        Order #<?php echo $order['order_id']; ?> - TK. <?php echo number_format($order['total_amount'], 2); ?> (<?php echo date('M d, Y', strtotime($order['order_date'])); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount (TK)</label>
                            <input type="number" name="amount" id="amount" class="form-control" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">Select payment method</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" name="submit_payment" class="btn-submit">Submit Payment</button>
                    </form>
                <?php else: ?>
                    <p class="text-center text-muted">No unpaid orders found.</p>
                <?php endif; ?>
            </div>

            <!-- Payment History -->
            <div class="payment-history">
                <h3>Payment History</h3>
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

            // Auto-close alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Update amount field based on selected order
            window.updateAmount = function(select) {
                const amountInput = document.getElementById('amount');
                const selectedOption = select.options[select.selectedIndex];
                const amount = selectedOption.getAttribute('data-amount');
                amountInput.value = amount ? parseFloat(amount).toFixed(2) : '';
            };
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>