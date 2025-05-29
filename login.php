<?php
require_once 'includes/messages.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

prevent_logged_in_access();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user = authenticate_user($_POST['username'] ?? '', $_POST['password'] ?? '');

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        $redirect = ($user['role'] === 'admin') ? 'admin/index.php' : 'index.php';
        header("Location: $redirect");
        exit();

    } catch (Exception $e) {
        set_error($e->getMessage());
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Airline Ticket Management System</title>
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/auth.css">
    <script src="libs/jquery-3.7.1.min.js"></script>
    <script src="scripts/toast.js"></script>
</head>

<body>
    <?php show_messages(); ?>

    <section class="auth-section">
        <div class="brand">
            <img src="assets/icons/logo.svg" alt="Airline Logo" width="50" />
            <h2>Airline System</h2>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Login</h2>
                </div>
                <div class="card-content">
                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input type="text" id="username" name="username" class="form-input" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" class="form-input" required>
                        </div>
                        <button type="submit" class="submit-button">Login</button>
                        <p>Don't have an account? <a href="register.php" class="link">Register
                                here</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>

</html>