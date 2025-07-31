<?php

include 'db_connect.php';
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity < 0) {
        $error = "Quantity cannot be negative.";
    } else {
        // Start transaction to prevent race conditions
        mysqli_begin_transaction($conn);
        try {
            // Check if item exists in inventory
            $sql = "SELECT COUNT(*) FROM inventory WHERE item_id = ? FOR UPDATE";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $item_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $count);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($count == 0) {
                // Insert new inventory record
                $sql = "INSERT INTO inventory (item_id, stock, last_updated) VALUES (?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $item_id, $quantity);
            } else {
                // Update existing inventory
                $sql = "UPDATE inventory SET stock = ?, last_updated = NOW() WHERE item_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $quantity, $item_id);
            }

            if (mysqli_stmt_execute($stmt)) {
                $success = "Inventory updated successfully!";
                error_log("Inventory updated: item_id=$item_id, new_stock=$quantity, user=" . ($_SESSION['user_id'] ?? 'unknown'));
                mysqli_commit($conn);
            } else {
                throw new Exception("Database error");
            }
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Failed to update inventory. Please try again.";
            error_log("Inventory update failed: item_id=$item_id, error=" . $e->getMessage());
        }
    }
}

// Fetch menu items with inventory data
$result = mysqli_query($conn, "
    SELECT m.item_id, m.name, i.stock
    FROM menu m
    LEFT JOIN inventory i ON m.item_id = i.item_id
");
$menuItems = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (!$result) {
    $error = "Error fetching menu: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Inventory - Golden Fork</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            height: 100vh;
            background: #1e293b;
            color: white;
            padding-top: 60px;
            position: fixed;
            width: 240px;
        }
        .sidebar a {
            color: #cbd5e1;
            display: block;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #334155;
            color: #fff;
        }
        .main-content {
            margin-left: 240px;
            padding: 40px 20px;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #0ea5e9;
        }
        .btn-update {
            background-color: #0ea5e9;
            color: white;
        }
        .btn-update:hover {
            background-color: #0284c7;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="text-center mb-4">
        <div class="brand-logo">🍴 Golden Fork</div>
    </div>
    <a href="index.php#menu">Menu</a>
    <a href="index.php#cart">Cart</a>
    <a href="index.php#analytics">Analytics</a>
    <a href="index.php#admin">Admin Panel</a>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</div>

<div class="main-content">
    <h2 class="mb-4 fw-bold text-center text-primary">Update Inventory</h2>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label for="item_id" class="form-label">Select Item</label>
                        <select class="form-select" name="item_id" id="item_id" required>
                            <option value="">Choose item</option>
                            <?php foreach ($menuItems as $item): ?>
                                <option value="<?= $item['item_id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?> (Stock: <?= $item['stock'] ?? 0 ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">New Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-update btn-lg w-100">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
