error_reporting(0);
ini_set('display_errors', 0);
<?php
// Database Configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', ''); // Leave empty if no password
define('DB_NAME', 'leave_management');

// Create Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Helper function to prevent SQL injection
function escape_string($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

// Helper function to fetch data
function fetch_data($query) {
    global $conn;
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Helper function to execute query
function execute_query($query) {
    global $conn;
    return $conn->query($query);
}
?>