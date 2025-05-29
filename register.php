<?php
require_once 'includes/messages.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'actions/users/user.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

prevent_logged_in_access();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        if (create_user($username, $email, $password, $full_name)) {
            set_success("Registration successful. You can now log in.");
            header("Location: login.php");
            exit();
        }
    } catch (Exception $e) {
        set_error($e->getMessage());
        header("Location: register.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Airline Ticket Management System</title>
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/auth.css">
    <script src="libs/jquery-3.7.1.min.js"></script>
    <script src="scripts/toast.js"></script>
</head>

<body>
    <?php show_messages(); ?>

    <section class="auth-section">
        <div class="brand">
            <img src="assets/icons/logo.svg" alt="Airline Logo" width="50">
            <h2>Airline System</h2>
        </div>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Register</h2>
                </div>
                <div class="card-content">
                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="full_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="full_name" name="full_name" class="form-input" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input type="text" id="username" name="username" class="form-input" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" class="form-input" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" class="form-input" required autofocus>
                        </div>
                        <button type="submit" class="submit-button">Register</button>
                        <p>Already have an account? <a href="login.php" class="link">Sign in
                                here</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>

</html>