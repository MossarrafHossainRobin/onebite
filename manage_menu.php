<?php

include 'db_connect.php';

ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$search = '';
$message = '';
$error = '';

// Create uploads directory
$target_dir = "Uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Add menu item
if (isset($_POST['add_menu'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = null;

    // Validate inputs
    if (empty($name) || empty($description) || !is_numeric($price) || !is_numeric($stock)) {
        $error = "All fields are required and must be valid.";
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            if (in_array($_FILES['image']['type'], $allowed_types) && $_FILES['image']['size'] <= $max_size) {
                $image = basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $image;
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            } else {
                $error = "Invalid image file. Use JPEG, PNG, or GIF under 5MB.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO menu (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $name, $description, $price, $stock, $image);
            if ($stmt->execute()) {
                $message = "Menu item added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Delete menu item
if (isset($_POST['delete_menu'])) {
    $item_id = $_POST['menu_id'];
    if (!empty($item_id)) {
        $stmt = $conn->prepare("DELETE FROM menu WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        if ($stmt->execute()) {
            $message = "Menu item deleted successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Menu ID is missing!";
    }
}

// Edit menu item
if (isset($_POST['edit_menu'])) {
    $item_id = $_POST['item_id'];
    $name = $_POST['edit_name'];
    $description = $_POST['edit_description'];
    $price = $_POST['edit_price'];
    $stock = $_POST['edit_stock'];
    $image = $_POST['existing_image'];

    // Validate inputs
    if (empty($name) || empty($description) || !is_numeric($price) || !is_numeric($stock)) {
        $error = "All fields are required and must be valid.";
    } else {
        // Handle image upload
        if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            if (in_array($_FILES['edit_image']['type'], $allowed_types) && $_FILES['edit_image']['size'] <= $max_size) {
                $image = basename($_FILES["edit_image"]["name"]);
                $target_file = $target_dir . $image;
                move_uploaded_file($_FILES["edit_image"]["tmp_name"], $target_file);
            } else {
                $error = "Invalid image file. Use JPEG, PNG, or GIF under 5MB.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("UPDATE menu SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE item_id = ?");
            $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image, $item_id);
            if ($stmt->execute()) {
                $message = "Menu item updated successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Search menu items
if (isset($_POST['search'])) {
    $search = trim($_POST['search_term']);
}
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE name LIKE ?");
    $search_param = "%" . $search . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $menu_result = $stmt->get_result();
    $stmt->close();
} else {
    $menu_sql = "SELECT * FROM menu";
    $menu_result = $conn->query($menu_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Menu - Golden Fork</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-navy: #1a2a44;
            --accent-gold: #d4a017;
            --ivory-bg: #f9f6f2;
            --text-dark: #1c2526;
            --hover-gold: #e6b800;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: var(--ivory-bg);
            color: var(--text-dark);
            line-height: 1.8;
        }

        header {
            background: linear-gradient(135deg, var(--primary-navy), var(--accent-gold));
            color: white;
            padding: 3rem 0;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: var(--ivory-bg);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(212, 160, 23, 0.2);
        }

        .card-img-top {
            height: 250px;
            object-fit: cover;
            border-bottom: 2px solid var(--accent-gold);
        }

        .card-header {
            background: var(--primary-navy);
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }

        .card-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.6rem;
            color: var(--primary-navy);
            font-weight: 600;
        }

        .card-text {
            color: #4a4a4a;
            font-size: 1rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            box-shadow: none;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 5px rgba(212, 160, 23, 0.3);
        }

        .btn-primary {
            background: var(--accent-gold);
            border: none;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--hover-gold);
            transform: translateY(-2px);
        }

        .btn-danger, .btn-warning {
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-danger:hover, .btn-warning:hover {
            transform: translateY(-2px);
        }

        .modal-header {
            background: var(--primary-navy);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .alert {
            border-radius: 8px;
            font-family: 'Open Sans', sans-serif;
        }

        .search-form .form-control {
            border-radius: 30px 0 0 30px;
        }

        .search-form .btn {
            border-radius: 0 30px 30px 0;
            background: var(--primary-navy);
            color: white;
            font-weight: 500;
        }

        .search-form .btn:hover {
            background: var(--accent-gold);
            color: var(--text-dark);
        }

        .back-btn {
            display: inline-block;
            margin: 2rem 0;
            background: var(--primary-navy);
            color: white !important;
            border-radius: 30px;
            padding: 0.8rem 2rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            text-transform: uppercase;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-align: center;
        }

        .back-btn:hover {
            background: var(--accent-gold);
            transform: translateY(-2px);
            color: var(--text-dark) !important;
        }

        footer {
            background: var(--primary-navy);
            color: var(--ivory-bg);
            text-align: center;
            padding: 1.5rem;
            font-size: 0.9rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }
            .card-img-top {
                height: 200px;
            }
            .card-title {
                font-size: 1.4rem;
            }
            .container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Manage Menu - Golden Fork</h1>
</header>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Add Menu Item Form -->
    <div class="card mb-4">
        <div class="card-header">Add New Menu Item</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price (TK)</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stock" name="stock" required>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image (JPEG, PNG, GIF, max 5MB)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                </div>
                <button type="submit" name="add_menu" class="btn btn-primary">Add Menu Item</button>
            </form>
        </div>
    </div>

    <!-- Search Form -->
    <form method="POST" class="d-flex mb-4 search-form">
        <input class="form-control me-0" type="search" placeholder="Search by name" aria-label="Search" name="search_term" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn" type="submit" name="search">Search</button>
    </form>

    <!-- Existing Menu Items -->
    <div class="row">
        <?php if ($menu_result && $menu_result->num_rows > 0): ?>
            <?php while ($row = $menu_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($row['image']) && file_exists('Uploads/' . $row['image'])): ?>
                            <img src="Uploads/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x250?text=No+Image" class="card-img-top" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-text"><strong>Price:</strong> TK. <?php echo number_format($row['price'], 2); ?></p>
                            <p class="card-text"><strong>Stock:</strong> <?php echo $row['stock']; ?></p>
                            <div class="d-flex justify-content-between">
 flourishing restaurant management system
                                <form method="POST">
                                    <input type="hidden" name="menu_id" value="<?php echo $row['item_id']; ?>">
                                    <button type="submit" name="delete_menu" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                                        onclick='editMenu(<?php echo json_encode($row); ?>)'>
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted fs-5">No menu items found.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back to Dashboard -->
    <div class="text-center">
        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Menu Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="edit_item_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="edit_description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price (TK)</label>
                        <input type="number" step="0.01" class="form-control" id="edit_price" name="edit_price" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="edit_stock" name="edit_stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Change Image (JPEG, PNG, GIF, max 5MB)</label>
                        <input type="file" class="form-control" id="edit_image" name="edit_image" accept="image/jpeg,image/png,image/gif">
                        <input type="hidden" id="existing_image" name="existing_image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_menu" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Golden Fork | All Rights Reserved</p>
</footer>

<!-- JavaScript to Fill Modal -->
<script>
    function editMenu(item) {
        document.getElementById('edit_item_id').value = item.item_id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_description').value = item.description;
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_stock').value = item.stock;
        document.getElementById('existing_image').value = item.image || '';
    }
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>