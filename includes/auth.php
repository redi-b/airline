<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/messages.php';

function authenticate_user($username, $password)
{
    global $conn;

    $username = trim($username);
    $password = trim($password);

    if (!$username || !$password) {
        throw new Exception("Both username and password are required.");
    }

    $stmt = $conn->prepare("SELECT user_id, username, password, role, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception("Invalid username or password.");
    }

    return $user;
}

function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function is_admin()
{
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login()
{
    validate_logged_in_user(false);

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        set_error("You are logged in as an admin. Please log in as a user.");
        header("Location: " . BASE_URL . "admin/index.php");
        exit();
    }
}

function require_admin()
{
    validate_logged_in_user(true);
}

function prevent_logged_in_access()
{
    if (is_logged_in()) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: " . BASE_URL . "admin/index.php");
        } else {
            header("Location: " . BASE_URL . "index.php");
        }
        exit();
    }
}

function get_user($conn)
{
    if (!is_logged_in()) {
        return null;
    }

    $stmt = $conn->prepare("SELECT user_id, username, email, full_name, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

function check_session_timeout()
{
    $timeout_duration = 1800; // 30 minutes in seconds
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        logout();
        set_warning("Session timed out due to inactivity. Please log in again.");
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

function logout()
{
    session_unset();
    session_destroy();
    session_regenerate_id(true);
    session_start();    // Start a new session for messages
}

function validate_logged_in_user($requireAdmin = false)
{

    if (!is_logged_in()) {
        set_error("Please log in to access this page.");
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    check_session_timeout();

    $conn = require_connection();
    if (!$conn) {
        set_error("Database connection failed. Please try again later.");
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        logout();
        set_error("Session expired or user no longer exists. Please log in again.");
        header("Location: " . BASE_URL . "login.php");
        exit();

    }

    if ($requireAdmin && $user['role'] !== 'admin') {
        set_error("Access denied. Admin privileges required.");
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

function require_connection()
{
    static $conn = null;

    if ($conn === null) {
        $conn = require dirname(__DIR__) . '/includes/db.php';
    }

    return $conn;
}
?>