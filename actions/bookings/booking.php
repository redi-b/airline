<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../flights/seat.php';
require_once __DIR__ . '/../payments/payment.php';

if (!$conn) {
    set_error("Database connection failed.");
    header("Location: ../../login.php");
    exit();
}

function book_flight($user_id, $flight_id, $seat_number, $passenger_name, $passenger_email)
{
    global $conn;

    // Get flight price and availability
    $stmt = $conn->prepare("SELECT price, available_seats FROM flights WHERE flight_id = ?");
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $stmt->bind_result($price, $available_seats);
    if (!$stmt->fetch()) {
        throw new Exception("Flight not found");
    }
    $stmt->close();

    if ($available_seats < 1) {
        throw new Exception("No available seats");
    }

    // Check seat availability
    $seat_check = $conn->prepare("SELECT is_booked FROM flight_seats WHERE flight_id = ? AND seat_number = ?");
    $seat_check->bind_param("is", $flight_id, $seat_number);
    $seat_check->execute();
    $seat_result = $seat_check->get_result()->fetch_assoc();
    $seat_check->close();

    if (!$seat_result || $seat_result['is_booked']) {
        throw new Exception("Seat already booked. Please select another.");
    }

    $conn->begin_transaction();

    try {
        // Decrease available seats
        $stmt = $conn->prepare("UPDATE flights SET available_seats = available_seats - 1 WHERE flight_id = ?");
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        $stmt->close();

        // Insert booking WITHOUT ticket number and status = 'pending'
        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, flight_id, passenger_name, passenger_email, total_price, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iissd", $user_id, $flight_id, $passenger_name, $passenger_email, $price);
        $stmt->execute();
        $booking_id = $stmt->insert_id;
        $stmt->close();

        book_seat($booking_id, $flight_id, $seat_number);
        create_pending_payment($booking_id, $price, 'pay_later');

        $conn->commit();

        return [
            'booking_id' => $booking_id,
            'total_price' => $price
        ];
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}



function cancel_booking($booking_id)
{
    global $conn;

    $conn->begin_transaction();

    try {
        // Get flight_id and total_price
        $stmt = $conn->prepare("SELECT flight_id, total_price FROM bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->bind_result($flight_id, $total_price);
        if (!$stmt->fetch())
            throw new Exception("Booking not found");
        $stmt->close();

        $stmt = $conn->prepare("UPDATE bookings 
                                SET status = 'cancelled', 
                                    eticket_url = NULL, 
                                    ticket_number = NULL,
                                    cancellation_fee = ?, 
                                    refund_amount = ? 
                                WHERE booking_id = ?");
        $cancellation_fee = round($total_price * 0.10, 2);
        $refund_amount = round($total_price - $cancellation_fee, 2);
        $stmt->bind_param("ddi", $cancellation_fee, $refund_amount, $booking_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE flight_seats 
                                SET is_booked = FALSE, booking_id = NULL 
                                WHERE booking_id = ? AND flight_id = ?");
        $stmt->bind_param("ii", $booking_id, $flight_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE flights 
                                SET available_seats = available_seats + 1 
                                WHERE flight_id = ?");
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE payments 
                                SET payment_status = 'refunded' 
                                WHERE booking_id = ? AND payment_status = 'paid'");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}



function get_user_bookings($user_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT b.*, f.flight_number, f.airline, f.origin, f.destination,
               f.departure_time, f.arrival_time,
               p.payment_status
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    $stmt->close();
    return $bookings;
}

function get_total_bookings()
{
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS count FROM bookings");
    return $result ? (int) $result->fetch_assoc()['count'] : 0;
}

function get_all_bookings()
{
    global $conn;

    $query = "
        SELECT b.booking_id, b.booking_date, b.status, b.passenger_name, b.passenger_email,
               b.total_price, b.ticket_number,
               f.flight_number, f.airline, f.origin, f.destination,
               f.departure_time, f.arrival_time
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN flights f ON b.flight_id = f.flight_id
        ORDER BY b.booking_date DESC
    ";

    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_booking($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT b.*, p.payment_status FROM bookings b 
        LEFT JOIN payments p ON b.booking_id = p.booking_id 
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    return $booking;
}

function get_booking_details($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT b.booking_id, b.booking_date, b.status, b.passenger_name, b.passenger_email,
               b.total_price, b.ticket_number,
               f.flight_number, f.airline, f.origin, f.destination,
               f.departure_time, f.arrival_time, p.payment_status
        FROM bookings b
        JOIN flights f ON b.flight_id = f.flight_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();

    return $booking;
}

function delete_booking($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();
}

function update_booking_status($booking_id, $booking_status, $payment_status = null)
{
    global $conn;

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $booking_status, $booking_id);
        $stmt->execute();
        $stmt->close();

        if ($payment_status !== null) {
            $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $payment_status, $booking_id);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
