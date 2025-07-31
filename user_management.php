<?php

// Include the database connection
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

// Initialize message variables
$message = '';
$error = '';

// Fetch all users from the database
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $error = "Error fetching users: " . mysqli_error($conn);
}

// Handle deactivate user
if (isset($_POST['delete_user_id'])) {
    $user_id = $_POST['delete_user_id'];
    $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $message = "User set to inactive successfully!";
    } else {
        $error = "Error updating user status: " . $stmt->error;
    }
    $stmt->close();
}

// Handle edit user
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($status) || empty($role)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET status = ?, role = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $status, $role, $user_id);
        if ($stmt->execute()) {
            $message = "User updated successfully!";
        } else {
            $error = "Error updating user: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch user for edit form
$edit_user = null;
if (isset($_GET['edit_user_id'])) {
    $edit_user_id = $_GET['edit_user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_user = $edit_result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management - Golden Fork</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-navy: #1a2a44;
            --accent-coral: #ff6f61;
            --ivory-bg: #f9f6f2;
            --text-dark: #1c2526;
            --hover-coral: #ff4b3a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--ivory-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, var(--primary-navy), #2a4066);
            color: white;
            padding: 3rem 0;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        header h1 {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        header .fork-icon {
            position: absolute;
            top: 1rem;
            left: 1.5rem;
            font-size: 2.5rem;
            color: var(--accent-coral);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-10deg); }
            50% { transform: translateY(-10px) rotate(10deg); }
        }

        .container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background: var(--primary-navy);
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .table tbody tr {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .table tbody tr:hover {
            background: var(--ivory-bg);
            transform: translateY(-2px);
        }

        .table-danger {
            background: rgba(255, 111, 97, 0.1);
        }

        .inactive-user {
            color: var(--accent-coral);
            font-weight: 700;
        }

        .btn-primary, .btn-danger {
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            text-transform: uppercase;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
        }

        .btn-primary {
            background: var(--accent-coral);
            border: none;
        }

        .btn-primary:hover {
            background: var(--hover-coral);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(255, 75, 58, 0.4);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .btn-dark {
            background: var(--primary-navy);
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-dark:hover {
            background: var(--accent-coral);
            transform: scale(1.05);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: var(--ivory-bg);
        }

        .form-select, .form-control {
            border-radius: 8px;
            box-shadow: none;
            transition: border-color 0.3s ease;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--accent-coral);
            box-shadow: 0 0 5px rgba(255, 111, 97, 0.3);
        }

        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: var(--primary-navy);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .alert {
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        footer {
            background: var(--primary-navy);
            color: var(--ivory-bg);
            text-align: center;
            padding: 1.5rem;
            font-size: 0.9rem;
            letter-spacing: 1px;
            position: sticky;
            bottom: 0;
            border-top: 3px solid var(--accent-coral);
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2.2rem;
            }
            .container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
            .table {
                font-size: 0.9rem;
            }
            .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
            .fork-icon {
                font-size: 2rem;
                top: 0.8rem;
                left: 1rem;
            }
        }

        @media (max-width: 576px) {
            header {
                padding: 2rem 1rem;
            }
            header h1 {
                font-size: 1.8rem;
            }
            .table {
                font-size: 0.85rem;
            }
            .btn-dark {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<header>
    <i class="fas fa-utensils fork-icon"></i>
    <h1>User Management</h1>
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

    <h3 class="mb-4">Manage Users</h3>

    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="<?php echo ($row['status'] == 'inactive') ? 'table-danger' : ''; ?>">
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td class="<?php echo ($row['status'] == 'inactive') ? 'inactive-user' : ''; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteUserId(<?php echo $row['user_id']; ?>)">Deactivate</button>
                            <a href="user_management.php?edit_user_id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No users found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($edit_user): ?>
        <div class="card p-4 my-4">
            <h4 class="mb-3">Edit User</h4>
            <form method="POST" action="user_management.php">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" <?php echo ($edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="customer" <?php echo ($edit_user['role'] == 'customer') ? 'selected' : ''; ?>>Customer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?php echo ($edit_user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($edit_user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="text-center">
        <a href="admin_dashboard.php" class="btn btn-dark">Back to Dashboard</a>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deactivation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to deactivate this user?
            </div>
            <div class="modal-footer">
                <form method="POST" action="user_management.php">
                    <input type="hidden" name="delete_user_id" id="delete_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deactivate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>© 2025 Golden Fork | All Rights Reserved</p>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
    function setDeleteUserId(userId) {
        document.getElementById('delete_user_id').value = userId;
    }
</script>
</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>