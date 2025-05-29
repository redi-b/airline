<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../actions/flights/seat.php';
require_once __DIR__ . '/../includes/messages.php';

function is_table_empty($table)
{
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS count FROM $table");
    if (!$result) {
        set_error("Error checking $table: " . $conn->error);
        return false;
    }
    $row = $result->fetch_assoc();
    return $row['count'] == 0;
}

function seed_users()
{
    global $conn;

    $users = [
        ['admin', 'admin@airline.et', 'admin123', 'System Admin', 'admin'],
        ['abebe_k', 'abebe.kebede@airline.et', 'abebe123', 'Abebe Kebede'],
        ['sara_b', 'sara.bekele@airline.et', 'sara123', 'Sara Bekele'],
        ['tesfaye_m', 'tesfaye.mekonnen@airline.et', 'tesfaye123', 'Tesfaye Mekonnen'],
        ['liya_a', 'liya.abera@airline.et', 'liya123', 'Liya Abera'],
    ];

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        set_error("User insert statement failed: " . $conn->error);
        return;
    }

    foreach ($users as $user) {
        $hashed = password_hash($user[2], PASSWORD_DEFAULT);
        $role = $user[4] ?? 'passenger';
        $stmt->bind_param("sssss", $user[0], $user[1], $hashed, $user[3], $role);
        if (!$stmt->execute()) {
            set_error("Error inserting user {$user[0]}: " . $stmt->error);
        }
    }

    $stmt->close();
    set_success("Users seeded successfully.");
}

function seed_flights()
{
    global $conn;

    require_once __DIR__ . '/../data/flights.php';


    $stmt = $conn->prepare("
        INSERT INTO flights (flight_number, airline, origin, destination, departure_time, arrival_time, available_seats, total_seats, price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        set_error("Flight insert statement failed: " . $conn->error);
        return;
    }

    foreach ($flights as $flight) {
        $stmt->bind_param("ssssssiii", ...$flight);
        if (!$stmt->execute()) {
            set_error("Error inserting flight {$flight[0]}: " . $stmt->error);
        } else {
            $flight_id = $stmt->insert_id;
            generate_seats_for_flight($flight_id, $flight[7]);
        }
    }

    $stmt->close();
    set_success("Flights seeded successfully.");
}

// Main seeding logic
if (is_table_empty('users')) {
    seed_users();
} else {
    echo "<p style='color: black;'>Users table is not empty, skipping.\n" . "</p>";
}

if (is_table_empty('flights')) {
    seed_flights();
} else {
    echo "<p style='color: black;'>Flights table is not empty, skipping.\n" . "</p>";
}
