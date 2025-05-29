<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/flights/flight.php';

check_session_timeout();
require_admin();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
            $flight_number = $_POST['flight_number'];
            $airline = $_POST['airline'];
            $origin = $_POST['origin'];
            $destination = $_POST['destination'];
            $departure_time = $_POST['departure_time'];
            $arrival_time = $_POST['arrival_time'];
            $available_seats = (int) $_POST['available_seats'];
            $total_seats = (int) $_POST['total_seats'];
            $price = (int) $_POST['price'];

            try {
                create_flight($flight_number, $airline, $origin, $destination, $departure_time, $arrival_time, $available_seats, $total_seats, $price);
                set_success("Flight created successfully.");
            } catch (Exception $e) {
                set_error("Error creating flight: " . $e->getMessage());
            }
            break;

        case 'delete':
            try {
                delete_flight((int) $_POST['flight_id']);
                set_success("Flight deleted successfully.");
            } catch (Exception $e) {
                set_error("Error deleting flight: " . $e->getMessage());
            }
            break;
    }
}

$flights = get_flights();

$page_title = "Manage Flights";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/flights.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/flight-form.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
    <h2 class="section-title">Manage Flights</h2>

    <section class="form-section card">
        <div class="card-header">
            <h3>Add New Flight</h3>
        </div>
        <div class="card-content">
            <form method="POST" class="flight-form">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="flight_number">Flight Number <span class="required">*</span></label>
                    <input type="text" name="flight_number" id="flight_number" placeholder="e.g., ET123" required>
                </div>
                <div class="form-group">
                    <label for="airline">Airline <span class="required">*</span></label>
                    <input type="text" name="airline" id="airline" placeholder="e.g., Ethiopian Airlines" required>
                </div>
                <div class="form-group">
                    <label for="origin">Origin <span class="required">*</span></label>
                    <input type="text" name="origin" id="origin" placeholder="e.g., Addis Ababa" required>
                </div>
                <div class="form-group">
                    <label for="destination">Destination <span class="required">*</span></label>
                    <input type="text" name="destination" id="destination" placeholder="e.g., Nairobi" required>
                </div>
                <div class="form-group">
                    <label for="departure_time">Departure Time <span class="required">*</span></label>
                    <input type="datetime-local" name="departure_time" id="departure_time" required>
                </div>
                <div class="form-group">
                    <label for="arrival_time">Arrival Time <span class="required">*</span></label>
                    <input type="datetime-local" name="arrival_time" id="arrival_time" required>
                </div>
                <div class="form-group">
                    <label for="available_seats">Available Seats <span class="required">*</span></label>
                    <input type="number" name="available_seats" id="available_seats" min="1" placeholder="e.g., 100"
                        required>
                </div>
                <div class="form-group">
                    <label for="total_seats">Total Seats <span class="required">*</span></label>
                    <input type="number" name="total_seats" id="total_seats" min="1" placeholder="e.g., 150" required>
                </div>
                <div class="form-group">
                    <label for="price">Price (Birr) <span class="required">*</span></label>
                    <input type="number" name="price" id="price" min="0" placeholder="e.g., 5000" required>
                </div>
                <button type="submit" class="submit-button">Create Flight</button>
            </form>
        </div>
    </section>

    <section class="flights-section">
        <h3 class="section-title">Existing Flights</h3>
        <?php if (count($flights) === 0): ?>
            <p class="no-flights">No flights found.</p>
        <?php else: ?>
            <div class="flights-grid">
                <?php foreach ($flights as $flight): ?>
                    <div class="flight-card card">
                        <div class="card-header">
                            <h4><?php echo htmlspecialchars($flight['flight_number']); ?></h4>
                            <span class="airline"><?php echo htmlspecialchars($flight['airline']); ?></span>
                        </div>
                        <div class="card-content">
                            <div class="flight-route">
                                <span><?php echo htmlspecialchars($flight['origin']); ?></span>
                                <img src="../assets/icons/plane-b.svg" alt="To" width="18" height="18" />
                                <span><?php echo htmlspecialchars($flight['destination']); ?></span>
                            </div>
                            <div class="flight-details">
                                <div class="detail">
                                    <span class="label">Departure:</span>
                                    <span><?php echo htmlspecialchars($flight['departure_time']); ?></span>
                                </div>
                                <div class="detail">
                                    <span class="label">Arrival:</span>
                                    <span><?php echo htmlspecialchars($flight['arrival_time']); ?></span>
                                </div>
                                <div class="detail">
                                    <span class="label">Seats:</span>
                                    <span><?php echo $flight['available_seats']; ?> /
                                        <?php echo $flight['total_seats']; ?></span>
                                </div>
                                <div class="detail">
                                    <span class="label">Price:</span>
                                    <span><?php echo number_format($flight['price'], 2); ?> Birr</span>
                                </div>
                            </div>
                            <div class="flight-actions">
                                <a href="edit-flight.php?id=<?php echo $flight['flight_id']; ?>" class="action-btn edit-btn"
                                    title="Edit Flight">
                                    <img src="../assets/icons/edit.svg" alt="Edit">
                                    <span>Edit</span>
                                </a>
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['flight_id']; ?>">
                                    <button type="submit" class="action-btn delete-btn" title="Delete Flight">
                                        <img src="../assets/icons/delete.svg" alt="Delete">
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
    $(document).ready(function () {
        $('.delete-form').on('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this flight?')) {
                $(this).off('submit').submit();
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>