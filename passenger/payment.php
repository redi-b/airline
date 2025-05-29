<?php
require_once '../includes/messages.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../actions/payments/payment.php';
require_once '../actions/bookings/booking.php';
require_once '../actions/flights/flight.php';
require_once '../helpers/eticket.php';

check_session_timeout();
require_login();

$booking_id = $_GET['booking_id'] ?? $_POST['booking_id'] ?? null;
if (!$booking_id) {
    set_error("Booking ID is missing.");
    header("Location: bookings.php");
    exit();
}

$booking = get_booking($booking_id);
if (!$booking) {
    set_error("Booking not found.");
    header("Location: bookings.php");
    exit();
}

if ($booking['payment_status'] === 'paid') {
    set_error("This booking is already paid.");
    header("Location: bookings.php");
    exit();
}

// Get flight details
$flight = get_flight_details($booking['flight_id']);
if (!$flight) {
    set_error("Flight not found.");
    header("Location: bookings.php");
    exit();
}

// Format dates
$booking_date = new DateTime($booking['booking_date']);
$booking['formatted_booking_date'] = $booking_date->format('l, M j, Y, H:i');
$departure = new DateTime($flight['departure_time']);
$flight['formatted_departure'] = $departure->format('l, M j, Y, H:i');
$expires_at = $booking['expires_at'] ? (new DateTime($booking['expires_at']))->format('l, M j, Y, H:i') : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'];
    $amount = $booking['total_price'];

    try {
        $payment_info = process_payment($booking_id, $amount, $method);

        if ($payment_info['status'] === "paid") {
            $ticket_path = generate_eticket($booking_id);

            $stmt = $conn->prepare("UPDATE bookings SET eticket_url = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $ticket_path, $booking_id);
            $stmt->execute();
            $stmt->close();

            set_success("Payment successful! Your e-ticket is ready.");
            header("Location: bookings.php");
            exit();
        } else if ($payment_info['status'] === "pending") {
            set_success("Complete payment to get your e-ticket.");
            header("Location: bookings.php");
            exit();
        }

    } catch (Exception $e) {
        set_error("Payment failed: " . $e->getMessage());
    }
}

$page_title = "Complete Payment";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/passenger/payment.css">
<?php include '../includes/header-end.php'; ?>

<section class="payment">
    <div class="container">
        <h2 class="section-title">Complete Your Payment</h2>
        <div class="payment-grid">
            <div class="payment-details card">
                <div class="card-header">
                    <h3>Booking Details</h3>
                </div>
                <div class="card-content">
                    <div class="detail-section">
                        <h4>Flight Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Flight:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($flight['airline']); ?>
                                (<?php echo htmlspecialchars($flight['flight_number']); ?>)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">From:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($flight['origin']); ?></span>
                            <span class="detail-label">To:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($flight['destination']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Depart:</span>
                            <span
                                class="detail-value"><?php echo htmlspecialchars($flight['formatted_departure']); ?></span>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h4>Booking Details</h4>
                        <div class="detail-row">
                            <span class="detail-label">Booking ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking_id); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Booked On:</span>
                            <span
                                class="detail-value"><?php echo htmlspecialchars($booking['formatted_booking_date']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span
                                class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Passenger:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($booking['passenger_name']); ?>
                                (<?php echo htmlspecialchars($booking['passenger_email']); ?>)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value"><?php echo number_format($booking['total_price'], 2); ?>
                                Birr</span>
                        </div>
                    </div>
                    <div class="detail-note">
                        <strong>Note:</strong> If you choose "Pay Later", your seat will be held for 48 hours.
                        <?php if ($expires_at): ?>Your booking expires on
                                <?php echo htmlspecialchars($expires_at); ?>.<?php endif; ?> If you don’t complete the
                        payment in time, your booking may be canceled.
                    </div>
                </div>
            </div>
            <div class="payment-form card">
                <div class="card-header">
                    <h3>Select Payment Method</h3>
                </div>
                <div class="card-content">
                    <form method="POST" action="" class="form">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
                        <div class="form-group">
                            <label for="payment_method">Choose a payment method:</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="pay_later">Pay Later</option>
                            </select>
                        </div>
                        <button type="submit" class="submit-button">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
</body>

</html>