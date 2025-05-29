<?php

require_once __DIR__ . '/../../includes/db.php';

function get_monthly_bookings(int $interval = 3): array
{
    global $conn;
    $sql = "
        SELECT DATE_FORMAT(booking_date, '%Y-%m') AS ym, COUNT(*) AS count
        FROM bookings
        WHERE booking_date BETWEEN DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND DATE_ADD(CURDATE(), INTERVAL ? MONTH)
        GROUP BY ym
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $interval, $interval);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return fill_missing_months($data, $interval);
}

function get_monthly_flights(int $interval = 3): array
{
    global $conn;
    $sql = "
        SELECT DATE_FORMAT(departure_time, '%Y-%m') AS ym, COUNT(*) AS count
        FROM flights
        WHERE departure_time BETWEEN DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND DATE_ADD(CURDATE(), INTERVAL ? MONTH)
        GROUP BY ym
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $interval, $interval);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return fill_missing_months($data, $interval);
}

function get_monthly_users(int $interval = 3): array
{
    global $conn;
    $sql = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS count
        FROM users
        WHERE created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND DATE_ADD(CURDATE(), INTERVAL ? MONTH)
        GROUP BY ym
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $interval, $interval);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    return fill_missing_months($data, $interval);
}

function fill_missing_months(array $raw_data, int $interval = 3): array
{
    $mapped = [];
    foreach ($raw_data as $row) {
        $mapped[$row['ym']] = (int) $row['count'];
    }

    $final = [];
    $base = strtotime(date('Y-m-01'));
    for ($i = -$interval; $i <= $interval; $i++) {
        $ts = strtotime("$i months", $base);
        $ym = date('Y-m', $ts);
        $label = date('M Y', $ts);
        $final[] = ['month' => $label, 'count' => $mapped[$ym] ?? 0];
    }

    return $final;
}

