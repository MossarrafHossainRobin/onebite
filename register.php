<?php

include 'db_connect.php';
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();
$error_message = '';
$success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST['user_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $selected_role = $_POST['role']; // Get selected role from dropdown

    // Basic server-side validation
    if (empty($user_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($selected_role)) {
        $_SESSION['error_message'] = "Please fill in all required fields and select a role.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $_SESSION['error_message'] = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
         $_SESSION['error_message'] = "Password and confirm password do not match.";
    } elseif (strlen($password) < 8) {
         $_SESSION['error_message'] = "Password must be at least 8 characters long.";
    } elseif (!in_array($selected_role, ['customer', 'admin'])) { // Validate selected role
         $_SESSION['error_message'] = "Invalid role selected.";
    } else {
         // Check if email or username already exists
        if (isset($conn)) {
            $check_query = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR user_name = ?");
            $check_query->bind_param("ss", $email, $user_name);
            $check_query->execute();
            $check_query->store_result();

            if ($check_query->num_rows > 0) {
                $_SESSION['error_message'] = "Email or Username already exists.";
            } else {
                 // Hash the password before storing it
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $status = 'active'; // Default status

                // Prepare SQL query to insert user data including phone, role, and status
                $query = $conn->prepare("INSERT INTO users (user_name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                // Use the selected_role variable
                $query->bind_param("ssssss", $user_name, $email, $hashed_password, $phone, $selected_role, $status);

                if ($query->execute()) {
                    $_SESSION['success_message'] = "Registration successful! You can now log in.";
                } else {
                    $_SESSION['error_message'] = "Error during registration: " . $query->error;
                }
                $query->close();
            }
            $check_query->close();
        } else {
             $_SESSION['error_message'] = "Database connection failed.";
        }
    }
    header("Location: register.php");
    exit();
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Golden Fork</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Lora:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --gf-primary-teal: #008080;
            --gf-accent-coral: #FF6F61;
            --gf-cream: #FFF8E7;
            --gf-dark: #2C3E50;
            --gf-text-light: #FFF;
            --gf-text-dark: #333;
            --gf-overlay: rgba(44, 62, 80, 0.5);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: url('bg2.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: var(--gf-text-dark);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gf-overlay);
            z-index: -1;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lora', serif;
            font-weight: 700;
            color: var(--gf-dark);
        }
        .navbar {
            background: rgba(44, 62, 80, 0.9);
            padding: 10px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-family: 'Lora', serif;
            font-size: 1.8rem;
            color: var(--gf-cream) !important;
        }
        .navbar-brand i {
            color: var(--gf-accent-coral);
            margin-left: 5px;
        }
        .navbar-nav .nav-link {
            color: var(--gf-text-light) !important;
            font-size: 1rem;
            margin: 0 10px;
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: var(--gf-accent-coral) !important;
        }
        .main-content {
            padding: 100px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px);
        }
        .register-container {
            background: var(--gf-cream);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            border: 2px solid var(--gf-accent-coral);
            transform: rotate(2deg);
            transition: transform 0.3s ease;
        }
        .register-container:hover {
            transform: rotate(0deg);
        }
        @media (max-width: 768px) {
            .register-container {
                padding: 20px;
                margin: 10px;
                transform: none;
            }
            .main-content {
                padding-top: 80px;
            }
        }
        .register-container h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--gf-dark);
        }
        .register-container .logo-text {
            font-family: 'Lora', serif;
            font-size: 2.5rem;
            color: var(--gf-primary-teal);
            margin-bottom: 15px;
        }
        .register-container .logo-text i {
            color: var(--gf-accent-coral);
            margin-left: 8px;
        }
        .register-container p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background: #fff;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--gf-primary-teal);
            outline: none;
            box-shadow: 0 0 5px rgba(0, 128, 128, 0.3);
        }
        .form-group label {
            position: absolute;
            top: 10px;
            left: 15px;
            font-size: 1rem;
            color: #999;
            transition: all 0.2s ease;
            pointer-events: none;
        }
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label {
            top: -20px;
            left: 10px;
            font-size: 0.8rem;
            color: var(--gf-primary-teal);
            background: var(--gf-cream);
            padding: 0 5px;
        }
        .form-group i.form-control-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
            font-size: 1rem;
        }
        .form-group input:not([type="password"]),
        .form-group select {
            padding-left: 40px;
        }
        .form-group .toggle-password-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 1rem;
        }
        .form-group .toggle-password-icon:hover {
            color: var(--gf-primary-teal);
        }
        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23333' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        .btn-primary {
            background: var(--gf-primary-teal);
            border: none;
            padding: 12px;
            font-size: 1.1rem;
            border-radius: 8px;
            color: var(--gf-text-light);
            width: 100%;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background: #006666;
            transform: translateY(-2px);
        }
        .alert {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .alert-danger {
            background: #ffe6e6;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }
        .alert-success {
            background: #e6fff7;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }
        .alert .btn-close {
            font-size: 0.8rem;
        }
        .login-link {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        .login-link a {
            color: var(--gf-accent-coral);
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        footer {
            background: var(--gf-dark);
            color: var(--gf-text-light);
            text-align: center;
            padding: 15px 0;
            position: relative;
            width: 100%;
        }
        footer p {
            margin: 0;
            font-size: 0.9rem;
        }
        .social-icons a {
            color: var(--gf-text-light);
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: var(--gf-accent-coral);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Golden Fork <i class="fas fa-utensils"></i></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                   
                  
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="register-container">
            <div class="logo-text">Golden Fork <i class="fas fa-utensils"></i></div>
            <h2>Sign Up</h2>
            <p>Create an account to enjoy our delicious offerings.</p>
            <?php
            if (isset($error_message) && $error_message != '') :
            ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
            endif;
            if (isset($success_message) && $success_message != '') :
            ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
            endif;
            ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <input type="text" id="user_name" name="user_name" required autocomplete="username" placeholder=" ">
                    <label for="user_name">Username</label>
                    <i class=" form-control"></i>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder=" ">
                    <label for="email">Email Address</label>
                    <i class=" form-control"></i>
                </div>
                <div class="form-group">
                    <input type="text" id="phone" name="phone" required autocomplete="tel" placeholder=" ">
                    <label for="phone">Phone Number</label>
                    <i class=" form-control"></i>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder=" ">
                    <label for="password">Password</label>
                    <i class="form-control"></i>
                    <i class="fas fa-eye toggle-password-icon" id="togglePassword"></i>
                </div>
                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder=" ">
                    <label for="confirm_password">Confirm Password</label>
                    <i class=" form-control"></i>
                    <i class="fas fa-eye toggle-password-icon" id="toggleConfirmPassword"></i>
                </div>
                <div class="form-group">
                    <select id="role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                    <label for="role">Role</label>
                    <i class=" form-control"></i>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <p class="login-link">Already have an account? <a href="login.php">Login Here</a></p>
        </div>
    </div>

    <footer>
        <p>© 2025 Golden Fork. All rights reserved.</p>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePasswordVisibility = (toggleId, inputId) => {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);
                if (toggle && input) {
                    toggle.addEventListener('click', function() {
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        this.classList.toggle('fa-eye');
                        this.classList.toggle('fa-eye-slash');
                    });
                }
            };
            togglePasswordVisibility('togglePassword', 'password');
            togglePasswordVisibility('toggleConfirmPassword', 'confirm_password');
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
                    if(bsAlert) {
                        bsAlert.hide();
                    }
                }, 7000);
            });
            const registrationForm = document.querySelector('.register-container form');
            if (registrationForm) {
                registrationForm.addEventListener('submit', function(event) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    if (password !== confirmPassword) {
                        event.preventDefault();
                        const alertHtml = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Password and Confirm Password do not match!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        registrationForm.insertAdjacentHTML('beforebegin', alertHtml);
                        const newAlert = registrationForm.previousElementSibling;
                        if(newAlert && newAlert.classList.contains('alert-danger')) {
                            newAlert.addEventListener('closed.bs.alert', function() {
                                newAlert.remove();
                            });
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>