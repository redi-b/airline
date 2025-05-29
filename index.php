<?php
require_once 'includes/messages.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'actions/flights/flight.php';
require_once 'data/flights.php';

check_session_timeout();
require_login();

$user = get_user($conn);
if (!$user) {
    set_error("Unable to fetch user details.");
    header("Location: login.php");
    exit();
}

$origins = get_all_origins();
$destinations = get_all_destinations();
$popular_routes = get_popular_routes(6);

$page_title = "Home";
?>

<?php include 'includes/header-start.php'; ?>
<link rel="stylesheet" href="styles/passenger/home.css">
<link rel="stylesheet" href="styles/passenger/flight-search.css">
<script src="scripts/passenger/suggestions.js"></script>
<script src="scripts/passenger/flightSearch.js"></script>
<script>
    const origins = <?php echo json_encode(array_column($origins, 'origin')); ?>;
    const destinations = <?php echo json_encode(array_column($destinations, 'destination')); ?>;
</script>
<?php include 'includes/header-end.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
        <p class="hero-subtitle">Plan your next journey with ease and explore the world!</p>

        <!-- Flight Search Form -->
        <div class="search-form-container">
            <form id="flight-search-form" action="passenger/flights.php" method="GET" class="flight-search-form">
                <div class="form-group">
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" placeholder="e.g. Addis Ababa"
                        value="<?php echo htmlspecialchars($filters['origin'] ?? ''); ?>" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" placeholder="e.g. Paris"
                        value="<?php echo htmlspecialchars($filters['destination'] ?? ''); ?>" required
                        autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="departure_date">Departure Date</label>
                    <input type="date" id="departure_date" name="departure_date"
                        value="<?php echo htmlspecialchars($filters['departure_date'] ?? ''); ?>">
                </div>
                <button type="submit" class="submit-button">Search Flights</button>
            </form>
            <a href="passenger/bookings.php" class="bookings-link link">View My Bookings</a>
        </div>
    </div>
</section>

<!-- Featured Destinations Section -->
<section class="destinations">
    <div class="container">
        <h2 class="section-title">Featured Destinations</h2>
        <div class="destinations-grid">
            <div class="destination-card card">
                <img src="assets/images/paris.jpg" alt="Paris" class="destination-image">
                <div class="destination-content card-content">
                    <h3>Paris</h3>
                    <p>Explore the city of love with its iconic landmarks and charming streets.</p>
                    <a href="passenger/flights.php?destination=Paris" class="link">Book Now</a>
                </div>
            </div>
            <div class="destination-card card">
                <img src="assets/images/tokyo.jpg" alt="Tokyo" class="destination-image">
                <div class="destination-content card-content">
                    <h3>Tokyo</h3>
                    <p>Discover vibrant culture and modern wonders in Japan's capital.</p>
                    <a href="passenger/flights.php?destination=Tokyo" class="link">Book Now</a>
                </div>
            </div>
            <div class="destination-card card">
                <img src="assets/images/dubai.jpg" alt="Dubai" class="destination-image">
                <div class="destination-content card-content">
                    <h3>Dubai</h3>
                    <p>Experience luxury and innovation in the heart of the desert.</p>
                    <a href="passenger/flights.php?destination=Dubai" class="link">Book Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Routes Section -->
<section class="popular-routes">
    <div class="container">
        <h2 class="section-title">Popular Routes</h2>
        <div class="popular-routes-grid">
            <?php if (empty($popular_routes)): ?>
                <p>No popular routes available at the moment.</p>
            <?php else: ?>
                <?php foreach ($popular_routes as $route): ?>
                    <div class="popular-routes-card card">
                        <div class="card-header flight-route">
                            <span><?php echo htmlspecialchars($route['origin']); ?></span>
                            <img src="assets/icons/plane-w.svg" alt="To" width="24" height="24" />
                            <span><?php echo htmlspecialchars($route['destination']); ?></span>
                        </div>
                        <div class="card-content">
                            <p>Fly from
                                <?php echo htmlspecialchars($city_descriptions[$route['origin']] ?? $route['origin']); ?> to
                                <?php echo htmlspecialchars($city_descriptions[$route['destination']] ?? $route['destination']); ?>.
                            </p>
                            <a href="passenger/flights.php?origin=<?php echo urlencode($route['origin']); ?>&destination=<?php echo urlencode($route['destination']); ?>"
                                class="link">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="why-choose-us">
    <div class="container">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="why-choose-us-grid">
            <div class="why-choose-us-card card">
                <div class="card-content">
                    <h3>Easy Booking</h3>
                    <p>Book your flights in just a few clicks with our user-friendly interface.</p>
                    <a href="passenger/flights.php" class="link">Start Booking</a>
                </div>
            </div>
            <div class="why-choose-us-card card">
                <div class="card-content">
                    <h3>24/7 Support</h3>
                    <p>Our team is available around the clock to assist with your travel needs.</p>
                    <a href="passenger/bookings.php" class="link">Manage Bookings</a>
                </div>
            </div>
            <div class="why-choose-us-card card">
                <div class="card-content">
                    <h3>Best Prices</h3>
                    <p>Enjoy competitive prices and exclusive deals on flights worldwide.</p>
                    <a href="passenger/flights.php" class="link">Find Deals</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>