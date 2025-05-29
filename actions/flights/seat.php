<?php
require_once __DIR__ . '/../../includes/db.php';

function get_flight_seats($flight_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT seat_number, is_booked FROM flight_seats WHERE flight_id = ? ORDER BY seat_number ASC");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $seats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $seats;
}

function generate_seats_for_flight($flight_id, $total_seats)
{
    global $conn;

    $seats_per_row = 6;
    $created = 0;
    $values = [];

    $row_index = 0;

    while ($created < $total_seats) {
        $row_label = get_excel_column_name($row_index);
        $row_index++;

        for ($i = 1; $i <= $seats_per_row; $i++) {
            $seat = $row_label . $i;
            $values[] = "($flight_id, '$seat')";
            $created++;
            if ($created >= $total_seats)
                break;
        }
    }

    if ($values) {
        $sql = "INSERT INTO flight_seats (flight_id, seat_number) VALUES " . implode(", ", $values);
        $conn->query($sql);
    }
}

function book_seat($booking_id, $flight_id, $seat_number)
{
    global $conn;

    $stmt = $conn->prepare("UPDATE flight_seats SET is_booked = TRUE, booking_id = ? WHERE flight_id = ? AND seat_number = ?");
    $stmt->bind_param("iis", $booking_id, $flight_id, $seat_number);
    $stmt->execute();
    $stmt->close();
}

function excel_row_index($label)
{
    $label = strtoupper($label);
    $index = 0;
    for ($i = 0; $i < strlen($label); $i++) {
        $index = $index * 26 + (ord($label[$i]) - ord('A') + 1);
    }
    return $index;
}


// Converts 0 => A, 1 => B, ..., 25 => Z, 26 => AA, ...
function get_excel_column_name($index)
{
    $name = '';
    while ($index >= 0) {
        $name = chr($index % 26 + 65) . $name;
        $index = intval($index / 26) - 1;
    }
    return $name;
}