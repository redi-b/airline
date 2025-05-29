<?php
require_once __DIR__ . '/messages.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$user = get_user($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        logout();
        set_info("You have been logged out successfully.");
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

?>

</head>

<body>
    <?php show_messages(); ?>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <img src="<?php echo BASE_URL; ?>assets/icons/logo.svg" alt="Airline Logo" width="40" />
                <h1 class="title">Airline System</h1>
            </div>
            <button class="navbar-toggle">
                <img src="<?php echo BASE_URL; ?>assets/icons/menu.svg" alt="Menu" class="menu-icon" />
                <img src="<?php echo BASE_URL; ?>assets/icons/close.svg" alt="Close" class="close-icon" />
            </button>
            <ul class="navbar-links">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/index.php" class="navbar-link">Dashboard</a></li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>admin/profile.php" class="navbar-link">
                                Profile
                                (<?php echo htmlspecialchars($user['username']); ?>)
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php" class="navbar-link">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>passenger/flights.php" class="navbar-link">Flights</a></li>
                        <li><a href="<?php echo BASE_URL; ?>passenger/bookings.php" class="navbar-link">My Bookings</a></li>
                        <li>
                            <a href="<?php echo BASE_URL; ?>passenger/profile.php" class="navbar-link">
                                Profile
                                (<?php echo htmlspecialchars($user['username']); ?>)
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <form method="POST">
                            <input type="hidden" name="logout">
                            <button type="submit" class="navbar-link navbar-link-logout">Logout</button>
                        </form>
                    </li>

                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php" class="navbar-link navbar-link-login">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>