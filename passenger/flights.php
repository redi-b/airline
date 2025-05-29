<?php
require_once '../includes/messages.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../actions/flights/flight.php';

check_session_timeout();
require_login();

$user = get_user($conn);
if (!$user) {
    set_error("Unable to fetch user details.");
    header("Location: ../login.php");
    exit();
}

$origins = get_all_origins();
$destinations = get_all_destinations();

$filters = [
    'origin' => $_GET['origin'] ?? '',
    'destination' => $_GET['destination'] ?? '',
    'departure_date' => $_GET['departure_date'] ?? '',
];

$flights = get_flights($filters, true);

// Format dates and calculate duration
foreach ($flights as &$flight) {
    $departure = new DateTime($flight['departure_time']);
    $arrival = new DateTime($flight['arrival_time']);
    $flight['formatted_departure'] = $departure->format('l, M j, Y, H:i');
    $flight['formatted_arrival'] = $arrival->format('l, M j, Y, H:i');
    $interval = $departure->diff($arrival);
    $hours = $interval->h + ($interval->days * 24);
    $minutes = $interval->i;
    $flight['duration'] = ($hours ? $hours . 'h ' : '') . ($minutes ? $minutes . 'm' : '');
}
unset($flight);

$page_title = "Flights";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/passenger/flights.css">
<link rel="stylesheet" href="../styles/passenger/flight-search.css">
<script src="../scripts/passenger/suggestions.js"></script>
<script src="../scripts/passenger/flightSearch.js"></script>
<script>
    const origins = <?php echo json_encode(array_column($origins, 'origin')); ?>;
    const destinations = <?php echo json_encode(array_column($destinations, 'destination')); ?>;
</script>
<?php include '../includes/header-end.php'; ?>

<!-- Header Section -->
<section class="header">
    <div class="container">
        <h1 class="header-title">Find Your Flight</h1>
        <p class="header-subtitle">Search for the best flights to your destination</p>

        <!-- Flight Search Form -->
        <div class="search-form-container">
            <form id="flight-search-form" action="flights.php" method="GET" class="flight-search-form">
                <div class="form-group">
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" placeholder="e.g. Addis Ababa"
                        value="<?php echo htmlspecialchars($filters['origin']); ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" placeholder="e.g. Paris"
                        value="<?php echo htmlspecialchars($filters['destination']); ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="departure_date">Departure Date</label>
                    <input type="date" id="departure_date" name="departure_date"
                        value="<?php echo htmlspecialchars($filters['departure_date']); ?>">
                </div>
                <button type="submit" class="submit-button">Search Flights</button>
            </form>
        </div>
    </div>
</section>

<!-- Flights Section -->
<section class="flights">
    <div class="container">
        <h2 class="section-title">
            <?php echo ($filters['origin'] || $filters['destination'] || $filters['departure_date']) ? "Search Results" : "All Flights"; ?>
        </h2>
        <?php if (empty($flights)): ?>
            <div class="no-results">
                <p>No flights found for your search. Please try different dates or destinations.</p>
                <a href="flights.php" class="link">Reset Search</a>
            </div>
        <?php else: ?>
            <div class="flights-grid">
                <?php foreach ($flights as $flight): ?>
                    <div class="flight-card card">
                        <div class="flight-content card-content">
                            <div class="flight-header">
                                <h3><?php echo htmlspecialchars($flight['airline']); ?></h3>
                                <span class="flight-number">
                                    <?php echo htmlspecialchars($flight['flight_number']); ?></span>
                            </div>
                            <div class="flight-route">
                                <p><?php echo htmlspecialchars($flight['origin']); ?></p>
                                <img src="../assets/icons/plane-b.svg" alt="To" width="24" height="24" />
                                <p><?php echo htmlspecialchars($flight['destination']); ?></p>
                            </div>
                            <div class="flight-details">
                                <div class="detail-item">
                                    <span class="label">Depart:</span>
                                    <span><?php echo htmlspecialchars($flight['formatted_departure']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Arrive:</span>
                                    <span><?php echo htmlspecialchars($flight['formatted_arrival']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Flight Duration:</span>
                                    <span><?php echo htmlspecialchars($flight['duration']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Seats Left:</span>
                                    <span
                                        class="<?php echo $flight['available_seats'] / $flight['total_seats'] < 0.2 ? 'low-seats' : ''; ?>">
                                        <?php echo htmlspecialchars($flight['available_seats']); ?> out of
                                        <?php echo htmlspecialchars($flight['total_seats']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flight-footer">
                                <span class="price"><?php echo htmlspecialchars(number_format($flight['price'], 2)); ?>
                                    Birr</span>
                                <form method="GET" action="../passenger/book.php">
                                    <input type="hidden" name="flight_id"
                                        value="<?php echo htmlspecialchars($flight['flight_id']); ?>">
                                    <button type="submit" class="book-button">Book Now</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
</body>

</html>