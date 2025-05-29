<?php
require_once __DIR__ . '/../../includes/db.php';

function get_all_users()
{
    global $conn;
    $result = $conn->query("SELECT * FROM users");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_user_by_id($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

function get_total_users()
{
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS count FROM users");
    return $result ? (int) $result->fetch_assoc()['count'] : 0;
}

function create_user($username, $email, $password, $full_name, $role = 'passenger')
{
    global $conn;

    // Trim input
    $username = trim($username);
    $email = trim($email);
    $full_name = trim($full_name);

    // Validate required fields
    if (!$username || !$email || !$password || !$full_name) {
        throw new Exception("All fields are required.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    // Validate password length
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $role);
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            throw new Exception("Username or email already exists.");
        } else {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}

function update_user($user_id, $fields)
{
    global $conn;
    $allowed = ['username', 'email', 'password', 'full_name', 'role', 'phone', 'address'];
    $set = [];
    $params = [];
    $types = '';

    foreach ($fields as $key => $value) {
        if (!in_array($key, $allowed))
            continue;
        if ($key === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }
        $set[] = "$key = ?";
        $params[] = $value;
        $types .= 's';
    }

    if (empty($set))
        return false;

    $params[] = $user_id;
    $types .= 'i';
    $sql = "UPDATE users SET " . implode(", ", $set) . " WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}


function delete_user($user_id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

function verify_user_password($user_id, $old_password)
{
    global $conn;
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    if ($stmt->fetch()) {
        return password_verify($old_password, $hashed);
    }
    return false;
}
