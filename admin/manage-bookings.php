<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/bookings/booking.php';

check_session_timeout();
require_admin();

$bookings = get_all_bookings();

$page_title = "Manage Bookings";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/bookings.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
    <h2 class="section-title">Manage Bookings</h2>

    <?php if (count($bookings) === 0): ?>
        <p class="no-bookings">No bookings found.</p>
    <?php else: ?>
        <div class="booking-grid">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card card">
                    <div class="card-header">
                        <h4>
                            <?php echo empty($booking['ticket_number'])
                                ? 'Not Ticketed Yet'
                                : 'Ticket #: ' .
                                htmlspecialchars($booking['ticket_number']);
                            ?>
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
                                <span class="label">Status:</span>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="booking-actions">
                            <a href="edit-booking.php?booking_id=<?php echo $booking['booking_id']; ?>"
                                class="action-btn view-btn" title="View Booking Details">
                                <img src="../assets/icons/view.svg" alt="View">
                                <span>View Details</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>