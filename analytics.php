<?php


require 'db_connect.php';
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch Menu
$result = mysqli_query($conn, "
    SELECT m.item_id, m.name, m.category, m.price, m.description, i.stock
    FROM menu m
    LEFT JOIN inventory i ON m.item_id = i.item_id
");
$menuItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching menu: " . mysqli_error($conn);
    exit;
}

// Fetch Orders
$result = mysqli_query($conn, "
    SELECT o.order_id, o.user_name, o.total_amount, o.order_date,
           GROUP_CONCAT(m.name) as items
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN menu m ON oi.item_id = m.item_id
    GROUP BY o.order_id
");

$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching orders: " . mysqli_error($conn);
    exit;
}

// Fetch Inventory
$result = mysqli_query($conn, "
    SELECT m.name, i.stock, i.last_updated
    FROM inventory i
    JOIN menu m ON i.item_id = i.item_id
");
$inventory = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching inventory: " . mysqli_error($conn);
    exit;
}

// Fetch Analytics Data
// Orders by Category
$result = mysqli_query($conn, "
    SELECT m.category, COUNT(*) as count
    FROM order_items oi
    JOIN menu m ON oi.item_id = m.item_id
    GROUP BY m.category
");

$ordersByCategory = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching category analytics: " . mysqli_error($conn);
    exit;
}

// Payment Methods
$result = mysqli_query($conn, "
    SELECT payment_method, COUNT(*) as count
    FROM payments
    GROUP BY payment_method
");
$paymentMethods = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching payment analytics: " . mysqli_error($conn);
    exit;
}

// Daily Orders
$result = mysqli_query($conn, "
    SELECT DATE(order_date) as date, COUNT(*) as count
    FROM orders
    GROUP BY DATE(order_date)
    ORDER BY date
");

$dailyOrders = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    echo "Error fetching daily orders: " . mysqli_error($conn);
    exit;
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addFLUX to_cart'])) {
    $item_id = (int)$_POST['item_id'];
    $result = mysqli_query($conn, "SELECT stock FROM inventory WHERE item_id = $item_id");
    $stock = mysqli_fetch_assoc($result)['stock'];
    
    if ($stock > 0) {
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity']++;
        } else {
            $result = mysqli_query($conn, "SELECT name, price FROM menu WHERE item_id = $item_id");
            $item = mysqli_fetch_assoc($result);
            $_SESSION['cart'][$item_id] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => 1
            ];
        }
    } else {
        $error = "Item out of stock!";
    }
}

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item_id => $item) {
        $total += $item['price'] * $item['quantity'];
        $result = mysqli_query($conn, "SELECT stock FROM inventory WHERE item_id = $item_id");
        $stock = mysqli_fetch_assoc($result)['stock'];
        if ($stock < $item['quantity']) {
            $error = "Insufficient stock for {$item['name']}";
            break;
        }
    }

    if (!isset($error)) {
        // Insert Order
        $result = mysqli_query($conn, "
            INSERT INTO `order` (user_name, user_id, total_amount, order_date)
            VALUES ('$user_name', 1, $total, NOW())
        ");
        if ($result) {
            $order_id = mysqli_insert_id($conn);

            // Insert Order Items and Update Inventory
            foreach ($_SESSION['cart'] as $item_id => $item) {
                $quantity = $item['quantity'];
                mysqli_query($conn, "
                    INSERT INTO order_items (order_id, item_id, quantity)
                    VALUES ($order_id, $item_id, $quantity)
                ");
                mysqli_query($conn, "
                    UPDATE inventory
                    SET stock = stock - $quantity, last_updated = NOW()
                    WHERE item_id = $item_id
                ");
            }

            // Insert Payment
            mysqli_query($conn, "
                INSERT INTO payments (order_id, user_id, amount, payment_method, payment_date)
                VALUES ($order_id, 1, $total, '$payment_method', NOW())
            ");

            $_SESSION['cart'] = [];
            header("Location: index.php?success=Order placed successfully!");
            exit;
        } else {
            $error = "Checkout failed: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Fork - Online Food Ordering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .gradient-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .fork-logo {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 3rem;
            color: #2dd4bf;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .menu-card {
            transition: transform 0.3s, box-shadow 0.3s;
            background: linear-gradient(135deg, #1e3a8a, #4b5e8a);
            color: white;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .cart-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 0;
        }
        .btn-coral {
            background-color: #ff6b6b;
            border-color: #ff6b6b;
        }
        .btn-coral:hover {
            background-color: #ff8787;
            border-color: #ff8787;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Header -->
    <header class="gradient-header p-6 text-center">
        <h1 class="text-4xl font-bold">Golden Fork</h1>
        <p class="text-lg mt-2">Order Your Favorite Meals Online</p>
        <span class="fork-logo">🍴</span>
    </header>

    <!-- Navigation -->
    <nav class="bg-blue-900 text-white p-4 sticky top-0 z-10">
        <div class="container mx-auto flex justify-between items-center">
            <ul class="flex space-x-4">
                <li><a href="admin_dashboard.php" class="hover:underline">⇐Back To Dasboard</a></li>
                <li><a href="#cart" class="hover:underline">Cart</a></li>
                <li><a href="#analytics" class="hover:underline">Analytics</a></li>
                <li><a href="#admin" class="hover:underline">Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
        <?php endif; ?>

        <!-- Menu Section -->
        <section id="menu" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Menu</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($menuItems as $item): ?>
                    <div class="menu-card p-4 rounded-lg shadow-md">
                        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p class="text-sm"><?php echo htmlspecialchars($item['description']); ?></p>
                        <p class="text-md font-semibold">$<?php echo number_format($item['price'], 2); ?></p>
                        <p class="text-sm <?php echo $item['stock'] > 0 ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php echo $item['stock'] > 0 ? "In Stock: {$item['stock']}" : 'Out of Stock'; ?>
                        </p>
                        <form method="post">
                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-coral text-white mt-2"
                                    <?php echo $item['stock'] > 0 ? '' : 'disabled'; ?>>Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Cart Section -->
        <section id="cart" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Your Cart</h2>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div id="cart-items" class="mb-4">
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $item_id => $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                        <div class="cart-item flex justify-between">
                            <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                            <span>$<?php echo number_format($item_total, 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p id="cart-total" class="text-lg font-semibold">Total: $<?php echo number_format($total, 2); ?></p>
                <button class="btn btn-coral text-white mt-4" data-bs-toggle="modal" data-bs-target="#checkoutModal"
                        <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>Proceed to Checkout</button>
            </div>
        </section>

        <!-- Analytics Section -->
        <section id="analytics" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Restaurant Analytics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-medium text-gray-700 mb-4">Order Volume by Category</h3>
                    <canvas id="barChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-medium text-gray-700 mb-4">Payment Method Distribution</h3>
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-medium text-gray-700 mb-4">Daily Order Trends</h3>
                    <canvas id="lineChart"></canvas>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-medium text-gray-700 mb-4">Inventory Stock Levels</h3>
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Admin Section -->
        <section id="admin" class="mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Admin Panel</h2>
            <div class="bgeal p-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-medium text-gray-700 mb-4">Recent Orders</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['items']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h3 class="text-xl font-medium text-gray-700 mt-6 mb-4">Inventory Status</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stock</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['stock']; ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($item['last_updated'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h3 class="text-xl font-medium text-gray-700 mt-6 mb-4">Users</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "SELECT user_id, user_name, email, phone, role, status FROM users");
                        $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        foreach ($users as $user):
                        ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td><?php echo $user['status']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="user_name" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <input type="text" class="form-control" id="delivery_address" name="delivery_address" required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="card">Credit/Debit Card</option>
                                <option value="cod">Cash on Delivery</option>
                            </select>
                        </div>
                        <div id="card-details" class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                        </div>
                        <button type="submit" name="checkout" class="btn btn-coral text-white">Place Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white p-4">
        <div class="container mx-auto text-center">
            <p>© 2025 Golden Fork. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Bar Chart: Order Volume by Category
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($ordersByCategory, 'category')) . "'"; ?>],
                datasets: [{
                    label: 'Order Volume',
                    data: [<?php echo implode(',', array_column($ordersByCategory, 'count')); ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Orders' } },
                    x: { title: { display: true, text: 'Category' } }
                }
            }
        });

        // Pie Chart: Payment Methods
        new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($paymentMethods, 'payment_method')) . "'"; ?>],
                datasets: [{
                    label: 'Payments',
                    data: [<?php echo implode(',', array_column($paymentMethods, 'count')); ?>],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 205, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        // Line Chart: Daily Order Trends
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($dailyOrders, 'date')) . "'"; ?>],
                datasets: [{
                    label: 'Orders',
                    data: [<?php echo implode(',', array_column($dailyOrders, 'count')); ?>],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Orders' } },
                    x: { title: { display: true, text: 'Date' } }
                }
            }
        });

        // Bar Chart: Inventory Stock Levels
        new Chart(document.getElementById('inventoryChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($inventory, 'name')) . "'"; ?>],
                datasets: [{
                    label: 'Stock',
                    data: [<?php echo implode(',', array_column($inventory, 'stock')); ?>],
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Stock' } },
                    x: { title: { display: true, text: 'Item' } }
                }
            }
        });

        // Payment Method Toggle
        document.getElementById('payment_method').addEventListener('change', function() {
            document.getElementById('card-details').style.display = this.value === 'card' ? 'block' : 'none';
        });
    </script>
</body>
</html>