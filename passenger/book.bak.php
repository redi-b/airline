<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../includes/db.php';
require_once '../actions/bookings/booking.php';
require_once '../actions/flights/flight.php';
require_once '../actions/flights/seat.php';

check_session_timeout();
require_login();

$user = get_user($conn);
if (!$user) {
    set_error("Unable to fetch user details.");
    header("Location: login.php");
    exit();
}

$user_id = $user['user_id'];
$name = $user['full_name'];
$email = $user['email'];

// Get flight details
$flight_id = $_GET['flight_id'] ?? null;
if (!$flight_id) {
    header("Location: flights.php");
    exit();
}

$flight = get_flight_details($flight_id);
if (!$flight) {
    set_error("Flight not found.");
    header("Location: flights.php");
    exit();
}

// Format dates
$departure = new DateTime($flight['departure_time']);
$arrival = new DateTime($flight['arrival_time']);
$flight['formatted_departure'] = $departure->format('l, M j, Y, H:i');
$flight['formatted_arrival'] = $arrival->format('l, M j, Y, H:i');

$seats = get_flight_seats($flight_id);
if (!$seats) {
    set_error("No seats available for this flight.");
    header("Location: flights.php");
    exit();
}

// Group seats by row letter
$seats_by_row = [];
foreach ($seats as $seat) {
    preg_match('/([A-Z]+)(\d+)/', $seat['seat_number'], $matches);
    if (count($matches) < 3) {
        error_log("Invalid seat_number: " . $seat['seat_number']);
        continue;
    }
    $row = $matches[1]; // Row letter (A-Z)
    $number = $matches[2]; // Seat number (1-6)
    $seats_by_row[$row][$number] = $seat;
}

// Get valid row letters
$row_letters = array_keys($seats_by_row);
if (empty($row_letters)) {
    set_error("No valid seats found for this flight.");
    header("Location: flights.php");
    exit();
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $seat_number = $_POST['seat'] ?? null;
        if (!$seat_number) {
            set_error("Seat selection is required.");
            header("Location: book.php?flight_id=$flight_id");
            exit();
        }

        $booking = book_flight($user_id, $flight_id, $seat_number, $name, $email);

        set_success("Booking successful! Complete your payment to get your e-ticket.");
        header("Location: payment.php?booking_id=" . $booking['booking_id']);
        exit();
    } catch (Exception $e) {
        set_error("Error booking flight: " . $e->getMessage());
    }
}

$page_title = "Book Flight";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/passenger/book.css">
<?php include '../includes/header-end.php'; ?>

<section class="booking container">
    <h2 class="section-title">Book Your Flight</h2>
    <div class="booking-grid">
        <div class="flight-details card">
            <div class="card-header">
                <h3><?php echo htmlspecialchars($flight['airline']); ?>
                    (<?php echo htmlspecialchars($flight['flight_number']); ?>)</h3>
            </div>
            <div class="card-content">
                <div class="detail-row">
                    <div class="flight-route">
                        <span><?php echo htmlspecialchars($flight['origin']); ?></span>
                        <img src="../assets/icons/plane-b.svg" alt="To" width="24" height="24" />
                        <span><?php echo htmlspecialchars($flight['destination']); ?></span>
                    </div>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Depart:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($flight['formatted_departure']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Arrive:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($flight['formatted_arrival']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Price:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(number_format($flight['price'], 2)); ?>
                        Birr</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Passenger:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($name); ?>
                        (<?php echo htmlspecialchars($email); ?>)</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Seats Left:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($flight['available_seats']); ?> of
                        <?php echo htmlspecialchars($flight['total_seats']); ?></span>
                </div>
            </div>
        </div>

        <form method="POST" action="" class="seat-selection-form">
            <h3 class="seat-title">Select Your Seat</h3>
            <div class="seat-legend">
                <span class="legend-item available">Available</span>
                <span class="legend-item selected">Selected</span>
                <span class="legend-item booked">Booked</span>
                <span class="legend-item unavailable">Unavailable</span>
            </div>

            <div class="seat-map">
                <?php foreach ($row_letters as $index => $row): ?>
                    <div class="seat-row">
                        <span class="row-number"><?php echo $index + 1; ?></span>
                        <div class="seat-group left">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <?php
                                $seat = $seats_by_row[$row][$i] ?? null;
                                $seat_number = $seat ? $seat['seat_number'] : '';
                                $status = $seat ? ($seat['is_booked'] ? 'booked' : 'available') : 'unavailable';
                                ?>
                                <?php if ($seat): ?>
                                    <div class="seat <?php echo $status; ?>"
                                        data-seat="<?php echo htmlspecialchars($seat_number); ?>">
                                        <?php echo htmlspecialchars($seat_number); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="seat unavailable" style="visibility: hidden;"></div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="aisle"></div>
                        <div class="seat-group right">
                            <?php for ($i = 4; $i <= 6; $i++): ?>
                                <?php
                                $seat = $seats_by_row[$row][$i] ?? null;
                                $seat_number = $seat ? $seat['seat_number'] : '';
                                $status = $seat ? ($seat['is_booked'] ? 'booked' : 'available') : 'unavailable';
                                ?>
                                <?php if ($seat): ?>
                                    <div class="seat <?php echo $status; ?>"
                                        data-seat="<?php echo htmlspecialchars($seat_number); ?>">
                                        <?php echo htmlspecialchars($seat_number); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="seat unavailable" style="visibility: hidden;"></div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="row-number"><?php echo $index + 1; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="seat" id="selected-seat" value="">
            <button type="submit" class="submit-button" disabled>Confirm Booking</button>
        </form>
    </div>
</section>


<script>
    $(document).ready(function () {
        let selectedSeat = null;

        // Use event delegation for dynamic seats
        $(document).on('click', '.seat.available', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                selectedSeat = null;
                $('#selected-seat').val('');
                $('.submit-button').prop('disabled', true);
            } else {
                $('.seat').removeClass('selected');
                $(this).addClass('selected');
                selectedSeat = $(this).data('seat');
                $('#selected-seat').val(selectedSeat);
                $('.submit-button').prop('disabled', false);
            }
        });

        // Ensure no initial selection
        $('.seat').removeClass('selected');
        $('#selected-seat').val('');
        $('.submit-button').prop('disabled', true);
    });
</script>
<?php include '../includes/footer.php'; ?>
</body>

</html>