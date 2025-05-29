<?php
require_once '../includes/messages.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../actions/flights/flight.php';
require_once '../actions/bookings/booking.php';
require_once '../actions/users/user.php';
require_once '../actions/analytics/analytics.php';

check_session_timeout();
require_admin();

$user = get_user($conn);
if (!$user) {
    set_error("Unable to fetch user details.");
    header("Location: login.php");
    exit();
}

$total_flights = get_total_flights();
$total_bookings = get_total_bookings();
$total_users = get_total_users();

$booking_stats = get_monthly_bookings();
$flight_stats = get_monthly_flights();
$user_stats = get_monthly_users();

$page_title = "Admin Dashboard";
?>

<?php include '../includes/header-start.php'; ?>
<link rel="stylesheet" href="../styles/admin/dashboard.css">
<link rel="stylesheet" href="../styles/admin/sidebar.css">
<script src="../libs/chart.umd.js"></script>
<script src="../scripts/admin/sidebar.js"></script>
<script>
    const chartData = {
        bookings: {
            labels: <?php echo json_encode(array_column($booking_stats, 'month')); ?>,
            data: <?php echo json_encode(array_column($booking_stats, 'count')); ?>
        },
        flights: {
            labels: <?php echo json_encode(array_column($flight_stats, 'month')); ?>,
            data: <?php echo json_encode(array_column($flight_stats, 'count')); ?>
        },
        users: {
            labels: <?php echo json_encode(array_column($user_stats, 'month')); ?>,
            data: <?php echo json_encode(array_column($user_stats, 'count')); ?>
        }
    };
</script>
<script src="../scripts/admin/charts.js"></script>
<?php include '../includes/header-end.php'; ?>

<div class="dashboard-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <div class="sidebar-header">
                <h2>Admin Dashboard</h2>
            </div>
            <nav class="sidebar-links">
                <a href="manage-flights.php" class="sidebar-link">
                    <img src="../assets/icons/flights.svg" alt="Manage Flights" width="24" height="24">
                    <p>Manage Flights</p>
                </a>
                <a href="manage-bookings.php" class="sidebar-link">
                    <img src="../assets/icons/bookings.svg" alt="Manage Bookings" width="24" height="24">
                    <p>Manage Bookings</p>
                </a>
                <a href="manage-users.php" class="sidebar-link">
                    <img src="../assets/icons/users.svg" alt="Manage Users" width="24" height="24">
                    <p>Manage Users</p>
                </a>
            </nav>
        </div>
        <button class="sidebar-toggle" id="sidebar-toggle">
            <img id="toggle-icon" src="../assets/icons/chevron-left.svg" alt="Collapse Sidebar Icon" width="24"
                height="24" height="24">
        </button>
    </aside>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>

        <div class="dashboard-cards">
            <div class="card card-flights" role="button" tabindex="0">
                <h3>Total Flights</h3>
                <p><?php echo $total_flights; ?></p>
            </div>
            <div class="card card-bookings" role="button" tabindex="0">
                <h3>Total Bookings</h3>
                <p><?php echo $total_bookings; ?></p>
            </div>
            <div class="card card-users" role="button" tabindex="0">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-container">
                <h3>Flights Overview</h3>
                <canvas id="flightsChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Bookings Overview</h3>
                <canvas id="bookingsChart"></canvas>
            </div>
            <div class="chart-container">
                <h3>Users Overview</h3>
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>

</html>