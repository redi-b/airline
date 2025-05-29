<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/messages.php';

// Connect to MySQL server
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$newDB = false;

try {
    // Try selecting the DB — will throw error if it doesn't exist
    $conn->select_db(DB_NAME);
} catch (mysqli_sql_exception $e) {
    // DB doesn't exist — create it
    if ($conn->query("CREATE DATABASE " . DB_NAME) === TRUE) {
        $newDB = true;
    } else {
        set_error('Error creating database: ' . $conn->error);
        exit();
    }

    // Select the new database
    $conn->select_db(DB_NAME);
}

// If it's a new DB, create and seed the tables
if ($newDB) {
    require_once __DIR__ . '/../db/schema.php';

    $statements = array_filter(array_map('trim', explode(';', $schema)));
    $errors = [];

    foreach ($statements as $stmt) {
        if (!empty($stmt) && $conn->query($stmt) !== TRUE) {
            $errors[] = "Schema error: " . $conn->error;
        }
    }

    if (!empty($errors)) {
        set_error("One or more errors occurred while setting up the database:<br>" . implode('<br>', array_map('htmlspecialchars', $errors)));
    } else {
        set_success("Database and tables created successfully.");
        require_once __DIR__ . '/../db/seed.php';
    }
}

return $conn;