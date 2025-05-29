<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

$title = "Airline Ticket Management System";
$page_title = isset($page_title) ? htmlspecialchars($page_title) . " | " . $title : $title;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/common.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles/footer.css">
    <script src="<?php echo BASE_URL; ?>libs/jquery-3.7.1.min.js"></script>
    <script src="<?php echo BASE_URL; ?>scripts/utils.js"></script>
    <script src="<?php echo BASE_URL; ?>scripts/toast.js"></script>
    <script src="<?php echo BASE_URL; ?>scripts/navbar.js"></script>