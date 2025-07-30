<?php

include 'db_connect.php';
ini_set('session.gc_maxlifetime', 14400); // 4 hours
session_set_cookie_params(14400);    
// Start session
session_start();

$admin_email = "admin@example.com";
$admin_password = "admin123";
$error_message = '';
$success_message = '';
$forgot_password_error = '';
$forgot_password_success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password_submit'])) {
    $forgot_email = $_POST['forgot_password_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($forgot_email) || empty($new_password) || empty($confirm_password)) {
        $forgot_password_error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $forgot_password_error = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 8) {
        $forgot_password_error = "New password must be at least 8 characters long.";
    } else {
        if (isset($conn) && $conn) {
            $query = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $query->bind_param("s", $forgot_email);
            $query->execute();
            $query->store_result();
            if ($query->num_rows > 0) {
                $query->fetch();
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update_query->bind_param("ss", $hashed_password, $forgot_email);
                if ($update_query->execute()) {
                    $forgot_password_success = "Your password has been updated successfully. You can now log in with your new password.";
                } else {
                    $forgot_password_error = "Error updating password. Please try again.";
                }
                if ($update_query) $update_query->close();
            } else {
                $forgot_password_error = "No user found with that email address.";
            }
            if ($query) $query->close();
        } else {
             $forgot_password_error = "Database connection failed.";
        }
    }
    $_SESSION['forgot_password_status'] = ['success' => $forgot_password_success, 'error' => $forgot_password_error];
    header("Location: login.php");
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['reset_password_submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        if ($email == $admin_email && $password == $admin_password) {
            $_SESSION['user_id'] = 1;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        } else {
            if (isset($conn) && $conn) {
                $query = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
                $query->bind_param("s", $email);
                $query->execute();
                $query->store_result();
                $query->bind_result($user_id, $stored_password, $role);
                if ($query->num_rows > 0) {
                    $query->fetch();
                    if (password_verify($password, $stored_password)) {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = $role;
                         if ($role == 'admin') {
                             header("Location: admin_dashboard.php");
                         } else {
                             header("Location: customer_dashboard.php");
                         }
                         exit();
                    } else {
                        $error_message = "Invalid email or password.";
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
                $query->close();
            } else {
                 $error_message = "Database connection failed.";
            }
        }
    }
}
if (isset($_SESSION['forgot_password_status'])) {
    $forgot_password_success = $_SESSION['forgot_password_status']['success'];
    $forgot_password_error = $_SESSION['forgot_password_status']['error'];
    unset($_SESSION['forgot_password_status']);
}
if (isset($conn) && $conn) {
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Golden Fork</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--gf-text-dark);
           

 overflow: hidden;
        }

        .login-container {
            background: rgba(0, 168, 150, 0.1); /* Semi-transparent teal background */
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
            backdrop-filter: blur(5px); /* Subtle blur effect */
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 2.5rem; /* Reduced logo size */
            color: var(--gf-accent-gold);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-text i {
            color: var(--gf-accent-teal);
            margin-left: 8px;
            font-size: 1.8rem; /* Reduced logo icon size */
            animation: floatSpoon 3s ease-in-out infinite;
        }

        @keyframes floatSpoon {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(3deg); }
            100% { transform: translateY(0) rotate(0deg); }
        }

        .login-container h2 {
            font-family: 'Merriweather', serif;
            font-size: 1.8rem;
            color: var(--gf-primary-dark);
            margin-bottom: 15px;
        }

        .login-container p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            font-weight: 500;
            color: var(--gf-text-dark);
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 40px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--gf-accent-teal);
            box-shadow: 0 0 8px rgba(0, 168, 150, 0.2);
            outline: none;
        }

        .form-group i.fa-envelope, .form-group i.fa-lock {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #999;
            font-size: 0.9rem; /* Reduced email and lock icon size */
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 0.9rem; /* Reduced eye icon size */
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: var(--gf-primary-dark);
        }

        .btn-primary {
            background: var(--gf-accent-burgundy);
            border: none;
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: #7a0b34;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .register-link, .forgot-password-link {
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .register-link a, .forgot-password-link a {
            color: var(--gf-accent-teal);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-link a:hover, .forgot-password-link a:hover {
            color: var(--gf-primary-dark);
            text-decoration: underline;
        }

        /* Modal Styles */
        .forgot-password-modal .modal-dialog {
            max-width: 500px;
        }

        .forgot-password-modal .modal-content {
            background: var(--gf-secondary-dark); /* Darker blue-gray background */
            color: var(--gf-text-light);
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: slideInModal 0.5s ease-out;
        }

        @keyframes slideInModal {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .forgot-password-modal .modal-header {
            border-bottom: none;
            padding: 20px 30px;
        }

        .forgot-password-modal .modal-title {
            font-family: 'Merriweather', serif;
            font-size: 1.5rem;
            color: var(--gf-accent-gold);
            display: flex;
            align-items: center;
        }

        .forgot-password-modal .modal-title i {
            margin-right: 8px;
            color: var(--gf-accent-teal);
            font-size: 1.2rem; /* Reduced modal logo icon size */
        }

        .forgot-password-modal .btn-close {
            filter: brightness(200%);
        }

        .forgot-password-modal .modal-body {
            padding: 30px;
        }

        .forgot-password-modal .form-label {
            color: var(--gf-text-light);
            font-weight: 500;
        }

        .forgot-password-modal .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--gf-text-light);
            padding: 12px 40px 12px 15px;
            border-radius: 10px;
        }

        .forgot-password-modal .form-control:focus {
            border-color: var(--gf-accent-teal);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 8px rgba(0, 168, 150, 0.2);
        }

        .forgot-password-modal .toggle-password {
            font-size: 0.9rem; /* Reduced eye icon size in modal */
        }

        .forgot-password-modal .modal-footer {
            border-top: none;
            padding: 20px 30px;
            justify-content: space-between;
        }

        .forgot-password-modal .btn-primary {
            background: var(--gf-accent-teal);
        }

        .forgot-password-modal .btn-primary:hover {
            background: #008774;
        }

        .forgot-password-modal .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--gf-text-light);
        }

        .forgot-password-modal .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 20px;
                width: 95%;
            }
            .logo-text {
                font-size: 2rem;
            }
            .logo-text i {
                font-size: 1.5rem;
            }
            .forgot-password-modal .modal-dialog {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-text">Golden Fork <i class="fas fa-utensils"></i></div>
        <h2>Sign In</h2>
        <p>Access your Golden Fork account</p>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($forgot_password_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $forgot_password_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($forgot_password_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $forgot_password_success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="register-link">New to Golden Fork? <a href="register.php">Create an Account</a></p>
        <p class="forgot-password-link"><a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a></p>
    </div>

    <div class="modal fade forgot-password-modal" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-utensils"></i> Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="login.php" id="forgotPasswordForm">
                    <div class="modal-body">
                        <p>Enter your email and new password to reset your account access.</p>
                        <div id="forgotPasswordModalAlerts"></div>
                        <div class="form-group">
                            <label for="forgotPasswordEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="forgotPasswordEmail" name="forgot_password_email" required autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required autocomplete="new-password">
                            <i class="fas fa-eye toggle-password" id="toggleNewPassword"></i>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required autocomplete="new-password">
                            <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="reset_password_submit">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePassword = (toggleId, inputId) => {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);
                if (toggle && input) {
                    toggle.addEventListener('click', () => {
                        const type = input.type === 'password' ? 'text' : 'password';
                        input.type = type;
                        toggle.classList.toggle('fa-eye');
                        toggle.classList.toggle('fa-eye-slash');
                    });
                }
            };

            togglePassword('togglePassword', 'password');
            togglePassword('toggleNewPassword', 'newPassword');
            togglePassword('toggleConfirmPassword', 'confirmPassword');

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 5000);
            });

            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const modalAlertsDiv = document.getElementById('forgotPasswordModalAlerts');
            if (forgotPasswordForm && modalAlertsDiv) {
                forgotPasswordForm.addEventListener('submit', (e) => {
                    const newPassword = document.getElementById('newPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    modalAlertsDiv.innerHTML = '';
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        modalAlertsDiv.innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Passwords do not match.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        const alert = modalAlertsDiv.querySelector('.alert');
                        alert.addEventListener('closed.bs.alert', () => {
                            modalAlertsDiv.innerHTML = '';
                        });
                    }
                });
            }

            <?php if ($forgot_password_success || $forgot_password_error): ?>
                const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
                modal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>