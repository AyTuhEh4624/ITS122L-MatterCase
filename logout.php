<?php
session_start();

// Include the database connection and audit log function
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/Functions/audit_log.php");

// Database credentials
$host = 'localhost';
$db_username = 'root';
$db_pass = '';
$database = 'mattercase';

// Connect to the database
$conn = mysqli_connect($host, $db_username, $db_pass, $database);

// Check for errors
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Log the logout action if the user is logged in
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $action = "User logged out";
    logAction($conn, $user_id, $action, $key, $method);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Close the database connection
mysqli_close($conn);

// Display logout message and redirect to login page
echo 'You logged out';
header('Refresh: 1; URL = login_page.php');
exit();
?>