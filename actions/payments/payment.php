<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/db.php';

function get_payment_by_booking_id($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM payments WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();

    return $payment;
}

function process_payment($booking_id, $amount, $method)
{
    global $conn;

    $conn->begin_transaction();

    try {
        if ($method === 'pay_later') {
            $existing_payment_id = find_pending_payment($booking_id);

            if ($existing_payment_id) {
                // Already pending
                $conn->commit();
                return [
                    'payment_id' => $existing_payment_id,
                    'status' => 'pending',
                    'message' => 'You have already selected Pay Later. Please complete the payment within 48 hours.'
                ];
            }

            $payment_id = create_pending_payment($booking_id, $amount, $method);
            $conn->commit();

            return [
                'payment_id' => $payment_id,
                'status' => 'pending',
                'message' => 'Payment pending. Please complete payment within 48 hours.'
            ];
        }

        $payment_id = find_pending_payment($booking_id);
        $transaction_id = strtoupper(uniqid("TKT"));

        if ($payment_id) {
            update_pending_payment_to_paid($payment_id, $amount, $method, $transaction_id);
        } else {
            $payment_id = insert_new_paid_payment($booking_id, $amount, $method, $transaction_id);
        }

        $ticket_number = confirm_booking_with_ticket($booking_id);
        $conn->commit();

        return [
            'payment_id' => $payment_id,
            'ticket_number' => $ticket_number,
            'amount' => $amount,
            'method' => $method,
            'status' => 'paid'
        ];

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}


// Helpers
function create_pending_payment($booking_id, $amount, $method)
{
    global $conn;
    $transaction_id = strtoupper(uniqid("TKT"));

    $stmt = $conn->prepare("
        INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("isds", $booking_id, $transaction_id, $amount, $method);
    $stmt->execute();
    $payment_id = $stmt->insert_id;
    $stmt->close();

    return $payment_id;
}

function find_pending_payment($booking_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id = ? AND payment_status = 'pending'");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($payment_id);
    $found = $stmt->fetch();
    $stmt->close();

    return $found ? $payment_id : null;
}

function update_pending_payment_to_paid($payment_id, $amount, $method, $transaction_id)
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE payments
        SET amount = ?, payment_method = ?, payment_status = 'paid', transaction_id = ?, payment_date = NOW()
        WHERE payment_id = ?
    ");
    $stmt->bind_param("dssi", $amount, $method, $transaction_id, $payment_id);
    $stmt->execute();
    $stmt->close();
}

function insert_new_paid_payment($booking_id, $amount, $method, $transaction_id)
{
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status, payment_date)
        VALUES (?, ?, ?, ?, 'paid', NOW())
    ");
    $stmt->bind_param("isds", $booking_id, $transaction_id, $amount, $method);
    $stmt->execute();
    $payment_id = $stmt->insert_id;
    $stmt->close();

    return $payment_id;
}

function confirm_booking_with_ticket($booking_id)
{
    global $conn;
    $ticket_number = strtoupper(uniqid("TKT"));

    $stmt = $conn->prepare("
        UPDATE bookings SET status = 'confirmed', ticket_number = ? WHERE booking_id = ?
    ");
    $stmt->bind_param("si", $ticket_number, $booking_id);
    $stmt->execute();
    $stmt->close();

    return $ticket_number;
}


