<?php
require_once '../includes/auth.php';
require_once '../includes/messages.php';
require_once '../actions/flights/flight.php';

check_session_timeout();
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id'])) {
        $flight_id = (int) $_POST['flight_id'];

        // Only update fields that are non-empty
        $fields = array_filter([
                'flight_number' => $_POST['flight_number'] ?? null,
                'airline' => $_POST['airline'] ?? null,
                'origin' => $_POST['origin'] ?? null,
                'destination' => $_POST['destination'] ?? null,
                'departure_time' => $_POST['departure_time'] ?? null,
                'arrival_time' => $_POST['arrival_time'] ?? null,
                'available_seats' => $_POST['available_seats'] !== '' ? (int) $_POST['available_seats'] : null,
                'total_seats' => $_POST['total_seats'] !== '' ? (int) $_POST['total_seats'] : null,
                'price' => $_POST['price'] !== '' ? (float) $_POST['price'] : null,
        ], fn($v) => $v !== null);

        try {
                update_flight($flight_id, $fields);
                set_success("Flight updated successfully.");
        } catch (Exception $e) {
                set_error("Error updating flight: " . $e->getMessage());
        }
}

$flight_id = $_GET['id'] ?? null;
$flight = $flight_id ? get_flight_details((int) $flight_id) : null;

if (!$flight) {
        set_error("Flight not found.");
        header("Location: manage-flights.php");
        exit();
}

$page_title = "Edit Flight";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/admin/flight-form.css">
<?php include '../includes/header-end.php'; ?>

<div class="container">
        <h2 class="section-title">Edit Flight</h2>

        <section class="form-section card">
                <div class="card-header">
                        <h3>Update Flight Details</h3>
                </div>
                <div class="card-content">
                        <form method="POST" class="flight-form">
                                <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                                <div class="form-group">
                                        <label for="flight_number">Flight Number <span class="required">*</span></label>
                                        <input type="text" name="flight_number" id="flight_number"
                                                placeholder="e.g., ET123"
                                                value="<?php echo htmlspecialchars($flight['flight_number']); ?>"
                                                required>
                                </div>
                                <div class="form-group">
                                        <label for="airline">Airline <span class="required">*</span></label>
                                        <input type="text" name="airline" id="airline"
                                                placeholder="e.g., Ethiopian Airlines"
                                                value="<?php echo htmlspecialchars($flight['airline']); ?>" required>
                                </div>
                                <div class="form-group">
                                        <label for="origin">Origin <span class="required">*</span></label>
                                        <input type="text" name="origin" id="origin" placeholder="e.g., Addis Ababa"
                                                value="<?php echo htmlspecialchars($flight['origin']); ?>" required>
                                </div>
                                <div class="form-group">
                                        <label for="destination">Destination <span class="required">*</span></label>
                                        <input type="text" name="destination" id="destination"
                                                placeholder="e.g., Nairobi"
                                                value="<?php echo htmlspecialchars($flight['destination']); ?>"
                                                required>
                                </div>
                                <div class="form-group">
                                        <label for="departure_time">Departure Time <span
                                                        class="required">*</span></label>
                                        <input type="datetime-local" name="departure_time" id="departure_time"
                                                value="<?php echo date('Y-m-d\TH:i', strtotime($flight['departure_time'])); ?>"
                                                required>
                                </div>
                                <div class="form-group">
                                        <label for="arrival_time">Arrival Time <span class="required">*</span></label>
                                        <input type="datetime-local" name="arrival_time" id="arrival_time"
                                                value="<?php echo date('Y-m-d\TH:i', strtotime($flight['arrival_time'])); ?>"
                                                required>
                                </div>
                                <div class="form-group">
                                        <label for="available_seats">Available Seats <span
                                                        class="required">*</span></label>
                                        <input type="number" name="available_seats" id="available_seats" min="1"
                                                placeholder="e.g., 100"
                                                value="<?php echo $flight['available_seats']; ?>" required>
                                </div>
                                <div class="form-group">
                                        <label for="total_seats">Total Seats <span class="required">*</span></label>
                                        <input type="number" name="total_seats" id="total_seats" min="1"
                                                placeholder="e.g., 150" value="<?php echo $flight['total_seats']; ?>"
                                                required>
                                </div>
                                <div class="form-group">
                                        <label for="price">Price (Birr) <span class="required">*</span></label>
                                        <input type="number" name="price" id="price" min="0" step="0.01"
                                                placeholder="e.g., 5000" value="<?php echo $flight['price']; ?>"
                                                required>
                                </div>
                                <button type="submit" class="submit-button">Update
                                        Flight</button>
                        </form>
                </div>
        </section>
</div>

<script>
        $(document).ready(function () {
                $('.submit-button').hover(
                        function () {
                                $(this).css('transform', 'scale(1.05)');
                        },
                        function () {
                                $(this).css('transform', 'scale(1)');
                        }
                );
        });
</script>

<?php include '../includes/footer.php'; ?>