<?php
require_once '../includes/messages.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../actions/bookings/booking.php';

check_session_timeout();
require_login();

$user = get_user($conn);
if (!$user) {
    set_error("Unable to fetch user details.");
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'cancel':
            try {
                cancel_booking($_POST['booking_id']);
                set_success("Booking cancelled successfully.");
            } catch (Exception $e) {
                set_error("Error: " . $e->getMessage());
            }
            break;
    }
}

$bookings = get_user_bookings($user['user_id']);

// Format dates and calculate duration
foreach ($bookings as &$booking) {
    $booking['formatted_booking_date'] = (new DateTime($booking['booking_date']))->format('l, M j, Y, H:i');
    $departure = new DateTime($booking['departure_time']);
    $arrival = new DateTime($booking['arrival_time']);
    $booking['formatted_departure'] = $departure->format('l, M j, Y, H:i');
    $booking['formatted_arrival'] = $arrival->format('l, M j, Y, H:i');
    $booking['formatted_expires_at'] = $booking['expires_at'] ? (new DateTime($booking['expires_at']))->format('l, M j, Y, H:i') : null;
    // Calculate duration
    $interval = $departure->diff($arrival);
    $hours = $interval->h + ($interval->days * 24);
    $minutes = $interval->i;
    $booking['duration'] = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');
}
unset($booking);

$page_title = "My Bookings";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/common.css">
<link rel="stylesheet" href="../styles/passenger/bookings.css">
<script src="../scripts/passenger/bookings.js"></script>
<?php include '../includes/header-end.php'; ?>

<section class="bookings">
    <div class="container">
        <h2 class="section-title">Your Bookings</h2>

        <?php if (count($bookings) > 0): ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card card accordion">
                                <div class="accordion-header">
                                    <div class="header-content">
                                        <h3><?php echo htmlspecialchars($booking['airline']); ?>
                                            (<?php echo htmlspecialchars($booking['flight_number']); ?>)</h3>
                                        <div class="flight-route">
                                            <p><?php echo htmlspecialchars($booking['origin']); ?></p>
                                            <img src="../assets/icons/plane-w.svg" alt="To" width="24" height="24" />
                                            <p><?php echo htmlspecialchars($booking['destination']); ?></p>
                                        </div>
                                        <p class="price">
                                            <?php echo number_format($booking['total_price'], 2); ?> Birr
                                        </p>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                        </span>
                                    </div>
                                    <div class="header-actions">
                                        <?php if ($booking['status'] === 'confirmed' && $booking['payment_status'] === 'paid'): ?>
                                                <form method="POST"
                                                    onsubmit="return confirm('Are you sure? Cancelling incurs a 10% penalty.');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                    <button type="submit" class="cancel-btn">Cancel</button>
                                                </form>
                                        <?php elseif ($booking['status'] === 'pending' && $booking['payment_status'] !== 'paid'): ?>
                                                <form method="GET" action="payment.php">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                    <button type="submit" class="pay-btn">Pay Now</button>
                                                </form>
                                        <?php else: ?>
                                                <button class="cancel-btn" disabled>Cancelled</button>
                                        <?php endif; ?>
                                        <?php if (!empty($booking['eticket_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($booking['eticket_url']); ?>" class="eticket-btn"
                                                    target="_blank">E-Ticket</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="accordion-body">
                                    <div class="detail-section">
                                        <h4>Flight Details</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Route:</span>
                                            <div class="detail-value flight-route-sm">
                                                <p><?php echo htmlspecialchars($booking['origin']); ?></p>
                                                <img src="../assets/icons/plane-b.svg" alt="To" width="16" height="16" />
                                                <p><?php echo htmlspecialchars($booking['destination']); ?></p>
                                            </div>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Depart:</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($booking['formatted_departure']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Arrive:</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($booking['formatted_arrival']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Duration:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($booking['duration']); ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-section">
                                        <h4>Booking Details</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Ticket #:</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($booking['ticket_number'] ?? '—'); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Booked On:</span>
                                            <span
                                                class="detail-value"><?php echo htmlspecialchars($booking['formatted_booking_date']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Passenger:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($booking['passenger_name']); ?>
                                                (<?php echo htmlspecialchars($booking['passenger_email']); ?>)</span>
                                        </div>
                                    </div>
                                    <div class="detail-section">
                                        <h4>Payment Status</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Amount:</span>
                                            <span class="detail-value"><?php echo number_format($booking['total_price'], 2); ?>
                                                Birr</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="detail-value">
                                                <span class="status-badge status-<?php echo $booking['payment_status']; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?>
                                                </span>
                                            </span>
                                        </div>
                                        <?php if ($booking['status'] === 'cancelled'): ?>
                                                <div class="detail-row">
                                                    <span class="detail-label">Cancellation Fee:</span>
                                                    <span class="detail-value"><?php echo number_format($booking['cancellation_fee'], 2); ?>
                                                        Birr</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="detail-label">Refund:</span>
                                                    <span class="detail-value"><?php echo number_format($booking['refund_amount'], 2); ?>
                                                        Birr</span>
                                                </div>
                                        <?php endif; ?>
                                        <?php if ($booking['payment_status'] === 'pending' && !empty($booking['formatted_expires_at'])): ?>
                                                <div class="detail-row">
                                                    <span class="detail-label">Expires:</span>
                                                    <span
                                                        class="detail-value"><?php echo htmlspecialchars($booking['formatted_expires_at']); ?></span>
                                                </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                    <?php endforeach; ?>
                </div>
        <?php else: ?>
                <div class="no-bookings card">
                    <div class="card-body">
                        <p>You have no bookings yet.</p>
                    </div>
                </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
</body>

</html>