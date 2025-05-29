<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/bookings/booking.php';
require_once '../actions/payments/payment.php';

check_session_timeout();
require_admin();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update':
            $booking_id = $_POST['booking_id'] ?? null;
            $new_status = $_POST['status'] ?? null;
            $payment_status = $_POST['payment_status'] ?? null;

            if (!$booking_id || !$new_status) {
                set_error("Missing required fields.");
            } else {
                try {
                    update_booking_status($booking_id, $new_status, $payment_status ?: null);
                    set_success("Booking status updated.");
                } catch (Exception $e) {
                    set_error("Update failed: " . $e->getMessage());
                }
            }
            break;

        case 'delete':
            try {
                delete_booking((int) $_POST['booking_id']);
                set_success("Booking deleted.");
                header("Location: manage-bookings.php");
                exit();
            } catch (Exception $e) {
                set_error("Failed to delete booking: " . $e->getMessage());
            }
            break;
    }
}

$booking_id = isset($_GET['booking_id']) ? (int) $_GET['booking_id'] : null;

if (!$booking_id) {
    set_error("No booking ID provided.");
    header("Location: manage-bookings.php");
    exit();
}

$booking = get_booking_details($booking_id);
$payment = get_payment_by_booking_id($booking_id);

if (!$booking) {
    set_error("Booking not found.");
    header("Location: manage-bookings.php");
    exit();
}

$page_title = "Booking Details";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/booking.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
    <h2 class="section-title">Booking Details</h2>

    <div class="booking-card card">
        <div class="card-header">
            <h4><?php echo empty($booking['ticket_number']) ? 'Not Ticketed Yet' : 'Booking #: ' . htmlspecialchars($booking['ticket_number']); ?>
            </h4>
        </div>
        <div class="card-content">
            <div class="booking-details">
                <div class="detail">
                    <span class="label">Passenger:</span>
                    <span><?php echo htmlspecialchars($booking['passenger_name']); ?></span>
                </div>
                <div class="detail">
                    <span class="label">Email:</span>
                    <span><?php echo htmlspecialchars($booking['passenger_email']); ?></span>
                </div>
                <div class="detail">
                    <span class="label">Flight:</span>
                    <div class="flight-info">
                        <span><?php echo htmlspecialchars($booking['flight_number']); ?> - </span>
                        <div class="flight-route">
                            <span><?php echo htmlspecialchars($booking['origin']); ?></span>
                            <img src="../assets/icons/plane-b.svg" alt="To" width="16" height="16" />
                            <span><?php echo htmlspecialchars($booking['destination']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="detail">
                    <span class="label">Departure:</span>
                    <span><?php echo htmlspecialchars($booking['departure_time']); ?></span>
                </div>
                <div class="detail">
                    <span class="label">Arrival:</span>
                    <span><?php echo htmlspecialchars($booking['arrival_time']); ?></span>
                </div>
                <div class="detail">
                    <span class="label">Total Price:</span>
                    <span><?php echo number_format($booking['total_price'], 2); ?> Birr</span>
                </div>
                <div class="detail detail-badge">
                    <span class="label">Status:</span>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                    </span>
                </div>
                <?php if ($payment): ?>
                    <div class="detail detail-badge">
                        <span class="label">Payment Status:</span>
                        <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                            <?php echo ucfirst(htmlspecialchars($payment['payment_status'])); ?>
                        </span>
                    </div>
                    <div class="detail">
                        <span class="label">Payment Method:</span>
                        <span><?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></span>
                    </div>
                    <div class="detail">
                        <span class="label">Transaction ID:</span>
                        <span><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                    </div>
                <?php else: ?>
                    <div class="detail">
                        <span class="label">Payment:</span>
                        <span><em>No payment record found</em></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="booking-actions">
                <form method="POST" class="update-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <div class="form-group">
                        <label for="status">Booking Status</label>
                        <select name="status" id="status">
                            <option value="confirmed" <?php if ($booking['status'] === 'confirmed')
                                echo 'selected'; ?>>
                                Confirmed</option>
                            <option value="cancelled" <?php if ($booking['status'] === 'cancelled')
                                echo 'selected'; ?>>
                                Cancelled</option>
                            <option value="pending" <?php if ($booking['status'] === 'pending')
                                echo 'selected'; ?>>
                                Pending</option>
                        </select>
                    </div>
                    <?php if ($payment): ?>
                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status">
                                <option value="paid" <?php if ($payment['payment_status'] === 'paid')
                                    echo 'selected'; ?>>Paid
                                </option>
                                <option value="pending" <?php if ($payment['payment_status'] === 'pending')
                                    echo 'selected'; ?>>Pending</option>
                                <option value="refunded" <?php if ($payment['payment_status'] === 'refunded')
                                    echo 'selected'; ?>>
                                    Refunded</option>
                                <option value="failed" <?php if ($payment['payment_status'] === 'failed')
                                    echo 'selected'; ?>>
                                    Failed</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="action-btn update-btn" title="Update Booking">
                        <img src="../assets/icons/edit.svg" alt="Update">
                        <span>Update Status</span>
                    </button>
                </form>

                <form method="POST" class="delete-form"
                    onsubmit="return confirm('Are you sure you want to delete this booking?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <button type="submit" class="action-btn delete-btn" title="Delete Booking">
                        <img src="../assets/icons/delete.svg" alt="Delete">
                        <span>Delete Booking</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>