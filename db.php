<?php
// db.php - connection (update credentials if needed for XAMPP)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'hostel_management';

// Create connection
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $DB_NAME");
mysqli_select_db($conn, $DB_NAME);
mysqli_set_charset($conn, 'utf8mb4');
?>