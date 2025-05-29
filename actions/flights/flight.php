<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/seat.php';

if (!$conn) {
    set_error("Database connection failed.");
    header("Location: ../../login.php");
    exit();
}

function get_flights($filters = [], $only_available = false)
{
    global $conn;

    $sql = "
        SELECT flight_id, flight_number, airline, origin, destination, 
               departure_time, arrival_time, available_seats, total_seats, price
        FROM flights
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    if ($only_available) {
        $sql .= " AND available_seats > 0 AND departure_time > NOW()";
    }

    if (!empty($filters['origin'])) {
        $sql .= " AND LOWER(origin) = LOWER(?)";
        $params[] = trim($filters['origin']);
        $types .= "s";
    }

    if (!empty($filters['destination'])) {
        $sql .= " AND LOWER(destination) = LOWER(?)";
        $params[] = trim($filters['destination']);
        $types .= "s";
    }

    if (!empty($filters['departure_date'])) {
        $sql .= " AND DATE(departure_time) = ?";
        $params[] = trim($filters['departure_date']);
        $types .= "s";
    }

    if (!empty($filters['order_by'])) {
        $allowed = ['price', 'departure_time', 'arrival_time', 'duration'];
        if (in_array($filters['order_by'], $allowed)) {
            $dir = strtoupper($filters['order_dir'] ?? 'ASC');
            $dir = in_array($dir, ['ASC', 'DESC']) ? $dir : 'ASC';
            if ($filters['order_by'] === 'duration') {
                $sql .= " ORDER BY TIMESTAMPDIFF(MINUTE, departure_time, arrival_time) $dir";
            } else {
                $sql .= " ORDER BY {$filters['order_by']} $dir";
            }
        }
    } else {
        $sql .= " ORDER BY departure_time ASC";
    }

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $flights = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $flights;
}

function get_flight_details($flight_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT flight_number, airline, origin, destination, departure_time, arrival_time, available_seats, total_seats, price
        FROM flights
        WHERE flight_id = ?
    ");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flight = $result->fetch_assoc();
    $stmt->close();

    return $flight;
}

// For Admin
function create_flight($flight_number, $airline, $origin, $destination, $departure_time, $arrival_time, $available_seats, $total_seats, $price)
{
    global $conn;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert flight
        $stmt = $conn->prepare("
            INSERT INTO flights (flight_number, airline, origin, destination, departure_time, arrival_time, available_seats, total_seats, price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssiii", $flight_number, $airline, $origin, $destination, $departure_time, $arrival_time, $available_seats, $total_seats, $price);
        $stmt->execute();
        $flight_id = $stmt->insert_id;
        $stmt->close();

        // Generate seats
        generate_seats_for_flight($flight_id, $total_seats);

        // Commit transaction
        $conn->commit();

        return $flight_id;
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        throw $e;
    }
}

function update_flight($flight_id, $fields)
{
    global $conn;

    $allowed_fields = [
        'flight_number',
        'airline',
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'available_seats',
        'total_seats',
        'price'
    ];

    $set_clauses = [];
    $params = [];
    $types = '';

    foreach ($fields as $key => $value) {
        if (in_array($key, $allowed_fields) && $value !== null && $value !== '') {
            $set_clauses[] = "$key = ?";
            $params[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }
    }

    if (empty($set_clauses)) {
        throw new Exception("No valid fields provided for update.");
    }

    $params[] = $flight_id;
    $types .= 'i';

    $query = "UPDATE flights SET " . implode(", ", $set_clauses) . " WHERE flight_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
}

function delete_flight($flight_id)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM flights WHERE flight_id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $stmt->close();
}


function get_total_flights()
{
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS count FROM flights");
    return $result ? (int) $result->fetch_assoc()['count'] : 0;
}

function get_all_origins()
{
    global $conn;
    $result = $conn->query("SELECT DISTINCT origin FROM flights ORDER BY origin ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_all_destinations()
{
    global $conn;
    $result = $conn->query("SELECT DISTINCT destination FROM flights ORDER BY destination ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_popular_routes($limit = 6)
{
    global $conn;

    $sql = "
        SELECT 
            f.origin, 
            f.destination, 
            COUNT(b.booking_id) AS bookings_count
        FROM flights f
        LEFT JOIN bookings b 
            ON f.flight_id = b.flight_id AND b.status = 'confirmed'
        GROUP BY f.origin, f.destination
        ORDER BY bookings_count DESC
        LIMIT ?
    ";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $routes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $routes;
    } else {
        return [];
    }
}
